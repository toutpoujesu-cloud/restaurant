<?php
/**
 * RAG Engine - Retrieval Augmented Generation
 * Native PHP implementation for document indexing and semantic search
 * Works with LangChain concepts using LLM embeddings
 */

if (!defined('ABSPATH')) {
    exit;
}

class RestaurantRAGEngine {
    private $llm_config;
    private $db;
    private $table_prefix;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->table_prefix = $wpdb->prefix;
        $this->llm_config = $this->get_llm_config();
    }

    /**
     * Get LLM configuration
     */
    private function get_llm_config() {
        $provider = get_option('restaurant_llm_provider', 'claude');
        $api_keys = get_option('restaurant_api_keys', []);

        return [
            'provider' => $provider,
            'api_keys' => $api_keys
        ];
    }

    /**
     * Initialize database tables for vector storage
     */
    public function init_database() {
        $charset_collate = $this->db->get_charset_collate();

        // Documents table
        $sql_documents = "CREATE TABLE IF NOT EXISTS {$this->table_prefix}rag_documents (
            id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            category varchar(100) NOT NULL,
            filename varchar(255) NOT NULL,
            content longtext NOT NULL,
            file_path varchar(500) NOT NULL,
            file_size bigint(20),
            file_type varchar(50),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY category (category),
            KEY filename (filename)
        ) $charset_collate;";

        // Document chunks table (for semantic search)
        $sql_chunks = "CREATE TABLE IF NOT EXISTS {$this->table_prefix}rag_chunks (
            id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            document_id bigint(20) NOT NULL,
            chunk_index int NOT NULL,
            content text NOT NULL,
            embedding longtext COMMENT 'JSON array of embedding vector',
            relevance_score float DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            KEY document_id (document_id),
            KEY chunk_index (chunk_index),
            FOREIGN KEY (document_id) REFERENCES {$this->table_prefix}rag_documents(id) ON DELETE CASCADE
        ) $charset_collate;";

        // Categories table
        $sql_categories = "CREATE TABLE IF NOT EXISTS {$this->table_prefix}rag_categories (
            id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name varchar(100) NOT NULL UNIQUE,
            description text,
            icon varchar(10),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            KEY name (name)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_documents);
        dbDelta($sql_chunks);
        dbDelta($sql_categories);
    }

    /**
     * Add knowledge base category
     */
    public function add_category($name, $description = '', $icon = '📁') {
        $exists = $this->db->get_var($this->db->prepare(
            "SELECT id FROM {$this->table_prefix}rag_categories WHERE name = %s",
            $name
        ));

        if ($exists) {
            return $exists;
        }

        $this->db->insert(
            $this->table_prefix . 'rag_categories',
            [
                'name' => $name,
                'description' => $description,
                'icon' => $icon
            ],
            ['%s', '%s', '%s']
        );

        return $this->db->insert_id;
    }

    /**
     * Get all categories
     */
    public function get_categories() {
        return $this->db->get_results(
            "SELECT * FROM {$this->table_prefix}rag_categories ORDER BY created_at DESC"
        );
    }

    /**
     * Upload and index document
     */
    public function upload_document($file_path, $category, $filename = '') {
        if (!file_exists($file_path)) {
            return ['success' => false, 'error' => 'File not found'];
        }

        $filename = $filename ?: basename($file_path);
        $file_size = filesize($file_path);
        $file_type = wp_check_filetype($filename)['ext'];
        $content = $this->extract_file_content($file_path);

        if (!$content) {
            return ['success' => false, 'error' => 'Could not extract content from file'];
        }

        // Insert document
        $this->db->insert(
            $this->table_prefix . 'rag_documents',
            [
                'category' => $category,
                'filename' => $filename,
                'content' => $content,
                'file_path' => $file_path,
                'file_size' => $file_size,
                'file_type' => $file_type
            ],
            ['%s', '%s', '%s', '%s', '%d', '%s']
        );

        $document_id = $this->db->insert_id;

        // Chunk and embed content
        $chunks = $this->chunk_content($content);
        foreach ($chunks as $index => $chunk) {
            $embedding = $this->get_embedding($chunk);
            
            $this->db->insert(
                $this->table_prefix . 'rag_chunks',
                [
                    'document_id' => $document_id,
                    'chunk_index' => $index,
                    'content' => $chunk,
                    'embedding' => json_encode($embedding)
                ],
                ['%d', '%d', '%s', '%s']
            );
        }

        return [
            'success' => true,
            'document_id' => $document_id,
            'filename' => $filename,
            'chunks' => count($chunks)
        ];
    }

    /**
     * Extract content from different file types
     */
    private function extract_file_content($file_path) {
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'txt':
                return file_get_contents($file_path);

            case 'pdf':
                return $this->extract_pdf_content($file_path);

            case 'csv':
                return $this->extract_csv_content($file_path);

            case 'doc':
            case 'docx':
                return $this->extract_docx_content($file_path);

            default:
                return file_get_contents($file_path);
        }
    }

    /**
     * Extract text from PDF
     */
    private function extract_pdf_content($file_path) {
        // Simple PDF extraction using regex
        $content = file_get_contents($file_path);
        
        // Remove binary data and extract text streams
        $content = preg_replace('/\x00/', '', $content);
        
        // Extract text between BT and ET markers (PDF text objects)
        if (preg_match_all('/BT(.*?)ET/s', $content, $matches)) {
            $text = implode("\n", $matches[1]);
            // Clean up PDF encoding
            $text = preg_replace('/[^a-zA-Z0-9\s\-\.\,\!\?\'\"\(\)]/u', '', $text);
            return $text;
        }

        return substr($content, 0, 5000); // Fallback: return first 5000 chars
    }

    /**
     * Extract data from CSV
     */
    private function extract_csv_content($file_path) {
        $content = '';
        if (($handle = fopen($file_path, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $content .= implode(' | ', $row) . "\n";
            }
            fclose($handle);
        }
        return $content;
    }

    /**
     * Extract text from DOCX
     */
    private function extract_docx_content($file_path) {
        // DOCX is a ZIP file, extract XML
        try {
            $zip = new ZipArchive();
            if ($zip->open($file_path) === true) {
                if (($xml = $zip->getFromName('word/document.xml')) !== false) {
                    // Remove XML tags and extract text
                    $content = strip_tags(str_replace(['</w:p>', '</w:t>'], ["\n", ''], $xml));
                    $zip->close();
                    return $content;
                }
                $zip->close();
            }
        } catch (Exception $e) {
            error_log('DOCX extraction error: ' . $e->getMessage());
        }
        return file_get_contents($file_path);
    }

    /**
     * Split content into chunks for semantic search
     */
    private function chunk_content($content, $chunk_size = 500, $overlap = 50) {
        $words = str_word_count($content, 1);
        $chunks = [];
        $current_chunk = [];

        foreach ($words as $word) {
            $current_chunk[] = $word;

            if (count($current_chunk) >= $chunk_size) {
                $chunks[] = implode(' ', $current_chunk);
                // Keep overlap for context
                $current_chunk = array_slice($current_chunk, -$overlap);
            }
        }

        // Add remaining words
        if (!empty($current_chunk)) {
            $chunks[] = implode(' ', $current_chunk);
        }

        return array_filter($chunks); // Remove empty chunks
    }

    /**
     * Get embedding vector from LLM
     * Uses selected LLM provider to generate embeddings
     */
    private function get_embedding($text) {
        $provider = $this->llm_config['provider'];
        $api_keys = $this->llm_config['api_keys'];

        switch ($provider) {
            case 'openai':
                return $this->get_openai_embedding($text, $api_keys['openai']);
            case 'claude':
                return $this->get_claude_embedding($text, $api_keys['claude']);
            case 'gemini':
                return $this->get_gemini_embedding($text, $api_keys['gemini']);
            default:
                // Return simple hash-based "embedding" for demo
                return $this->get_simple_embedding($text);
        }
    }

    /**
     * Get OpenAI embeddings
     */
    private function get_openai_embedding($text, $api_key) {
        $response = wp_remote_post('https://api.openai.com/v1/embeddings', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'input' => substr($text, 0, 2000),
                'model' => 'text-embedding-3-small'
            ]),
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            error_log('OpenAI embedding error: ' . $response->get_error_message());
            return $this->get_simple_embedding($text);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body['data'][0]['embedding'] ?? $this->get_simple_embedding($text);
    }

    /**
     * Get Claude embeddings (via simple hash for now)
     */
    private function get_claude_embedding($text, $api_key) {
        // Claude doesn't have native embeddings, use simple method
        return $this->get_simple_embedding($text);
    }

    /**
     * Get Gemini embeddings
     */
    private function get_gemini_embedding($text, $api_key) {
        $response = wp_remote_post(
            'https://generativelanguage.googleapis.com/v1beta/models/embedding-001:embedContent',
            [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'model' => 'models/embedding-001',
                    'content' => ['parts' => [['text' => substr($text, 0, 2000)]]]
                ]),
                'timeout' => 30
            ]
        );

        if (is_wp_error($response)) {
            return $this->get_simple_embedding($text);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body['embedding']['values'] ?? $this->get_simple_embedding($text);
    }

    /**
     * Simple embedding using text hashing
     * Used as fallback or for demo purposes
     */
    private function get_simple_embedding($text) {
        // Create a pseudo-embedding vector (384-dim like common models)
        $hash = hash('sha256', $text);
        $vector = [];
        
        for ($i = 0; $i < 384; $i++) {
            $vector[] = (hexdec(substr($hash, $i % 64, 2)) / 255) - 0.5;
        }
        
        return $vector;
    }

    /**
     * Retrieve relevant documents using semantic search
     */
    public function retrieve_relevant_documents($query, $top_k = 5) {
        $query_embedding = $this->get_embedding($query);
        
        // Get all chunks with embeddings
        $chunks = $this->db->get_results(
            "SELECT c.*, d.filename, d.category FROM {$this->table_prefix}rag_chunks c
             JOIN {$this->table_prefix}rag_documents d ON c.document_id = d.id
             WHERE c.embedding IS NOT NULL"
        );

        if (empty($chunks)) {
            return [];
        }

        // Calculate similarity scores using cosine similarity
        $scored_chunks = [];
        foreach ($chunks as $chunk) {
            $chunk_embedding = json_decode($chunk->embedding, true);
            $similarity = $this->cosine_similarity($query_embedding, $chunk_embedding);
            
            $chunk->similarity_score = $similarity;
            $scored_chunks[] = $chunk;
        }

        // Sort by similarity score
        usort($scored_chunks, function($a, $b) {
            return $b->similarity_score <=> $a->similarity_score;
        });

        // Return top-k
        return array_slice($scored_chunks, 0, $top_k);
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    private function cosine_similarity($vec1, $vec2) {
        if (empty($vec1) || empty($vec2)) {
            return 0;
        }

        $dot_product = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        for ($i = 0; $i < min(count($vec1), count($vec2)); $i++) {
            $dot_product += $vec1[$i] * $vec2[$i];
            $magnitude1 += $vec1[$i] ** 2;
            $magnitude2 += $vec2[$i] ** 2;
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }

        return $dot_product / ($magnitude1 * $magnitude2);
    }

    /**
     * Get document by ID
     */
    public function get_document($document_id) {
        return $this->db->get_row($this->db->prepare(
            "SELECT * FROM {$this->table_prefix}rag_documents WHERE id = %d",
            $document_id
        ));
    }

    /**
     * Delete document
     */
    public function delete_document($document_id) {
        return $this->db->delete(
            $this->table_prefix . 'rag_documents',
            ['id' => $document_id],
            ['%d']
        );
    }

    /**
     * Get all documents in category
     */
    public function get_documents_by_category($category) {
        return $this->db->get_results($this->db->prepare(
            "SELECT id, filename, file_size, file_type, created_at, category
             FROM {$this->table_prefix}rag_documents
             WHERE category = %s
             ORDER BY created_at DESC",
            $category
        ));
    }
}

// Initialize RAG Engine on theme load
add_action('after_setup_theme', function() {
    global $restaurant_rag_engine;
    $restaurant_rag_engine = new RestaurantRAGEngine();
    $restaurant_rag_engine->init_database();
});
