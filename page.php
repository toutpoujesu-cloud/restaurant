<?php
/**
 * Template for displaying pages
 */

get_header(); ?>

<div class="page-content" style="margin-top: 100px; padding: 3rem 0;">
    <div class="container">
        <?php
        while ( have_posts() ) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <h1 class="entry-title" style="font-family: var(--font-display); font-size: 3rem; margin-bottom: 2rem;">
                        <?php the_title(); ?>
                    </h1>
                </header>

                <div class="entry-content" style="background: white; padding: 2rem; border-radius: 12px;">
                    <?php the_content(); ?>
                </div>
            </article>
            <?php
        endwhile;
        ?>
    </div>
</div>

<?php get_footer(); ?>
