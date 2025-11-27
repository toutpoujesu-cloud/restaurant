<section class="menu-section" id="menu-section">
    <div class="container">
        <h2 class="section-title">OUR MENU</h2>
        
        <div class="menu-tabs">
            <button class="menu-tab active" data-category="chicken">Fried Chicken</button>
            <button class="menu-tab" data-category="wings">Wednesday Wings</button>
            <button class="menu-tab" data-category="catfish">Friday Catfish</button>
        </div>
        
        <!-- Chicken Menu -->
        <div class="menu-category active" id="chicken-menu">
            <div class="meal-cards">
                <!-- Wing Combo -->
                <div class="meal-card">
                    <div class="card-image" style="background-image: url('https://images.unsplash.com/photo-1567620832903-9fc6debc209f?ixlib=rb-4.0.1&auto=format&fit=crop&w=800&q=80');"></div>
                    <div class="card-content">
                        <div class="badge">Perfect Start</div>
                        <h3>Wing Combo</h3>
                        <p class="card-price">€12</p>
                        <p>Get your fix with 10 pieces of golden, crispy wings. Each piece is hand-breaded with our secret family recipe!</p>
                        <ul class="card-features">
                            <li><i class="fas fa-check"></i> 10 Signature Wings</li>
                            <li><i class="fas fa-check"></i> 2 Sides of Your Choice</li>
                            <li><i class="fas fa-check"></i> 2 Dipping Sauces</li>
                        </ul>
                        <button class="btn customize-btn" data-meal="Wing Combo" data-price="12">CUSTOMIZE & ORDER</button>
                    </div>
                </div>

                <!-- Family Box -->
                <div class="meal-card meal-card-popular">
                    <div class="card-badge">
                        <div class="badge" style="background-color: var(--color-primary); color: white;">MOST POPULAR</div>
                    </div>
                    <div class="card-image" style="background-image: url('https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?ixlib=rb-4.0.1&auto=format&fit=crop&w=800&q=80');"></div>
                    <div class="card-content">
                        <div class="badge">Best Value</div>
                        <h3>Family Box</h3>
                        <p class="card-price">€21</p>
                        <p>Our bestseller! 20 pieces of mouthwatering fried chicken. Crispy, seasoned, and made fresh just for you.</p>
                        <ul class="card-features">
                            <li><i class="fas fa-check"></i> 20 Pieces Chicken</li>
                            <li><i class="fas fa-check"></i> 2 Large Sides</li>
                            <li><i class="fas fa-check"></i> 2 Signature Sauces</li>
                        </ul>
                        <button class="btn customize-btn" data-meal="Family Box" data-price="21">CUSTOMIZE & ORDER ➤</button>
                    </div>
                </div>

                <!-- Mega Feast -->
                <div class="meal-card">
                    <div class="card-image" style="background-image: url('https://images.unsplash.com/photo-1608039755401-742074f0548d?ixlib=rb-4.0.1&auto=format&fit=crop&w=800&q=80');"></div>
                    <div class="card-content">
                        <div class="badge">Party Size</div>
                        <h3>Mega Feast</h3>
                        <p class="card-price">€30</p>
                        <p>Feed the whole crew! 30 pieces of our legendary chicken plus 3 sides. Perfect for parties, squad nights, or serious leftovers.</p>
                        <ul class="card-features">
                            <li><i class="fas fa-check"></i> 30 Pieces Chicken</li>
                            <li><i class="fas fa-check"></i> 3 Large Sides</li>
                            <li><i class="fas fa-check"></i> 3 Signature Sauces</li>
                        </ul>
                        <button class="btn customize-btn" data-meal="Mega Feast" data-price="30">CUSTOMIZE & ORDER</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Wednesday Wings Menu -->
        <div class="menu-category" id="wings-menu">
            <div class="meal-cards">
                <div class="meal-card meal-card-popular">
                    <div class="card-badge">
                        <div class="badge" style="background-color: var(--color-primary); color: white;">WEDNESDAY SPECIAL</div>
                    </div>
                    <div class="card-image" style="background-image: url('https://images.unsplash.com/photo-1567620832903-9fc6debc209f?ixlib=rb-4.0.1&auto=format&fit=crop&w=800&q=80');"></div>
                    <div class="card-content">
                        <h3>Wednesday Wing Deal</h3>
                        <p class="card-price">€12</p>
                        <p>Wednesday special! 10 pieces of our signature wings with 2 sides. Perfect mid-week treat!</p>
                        <ul class="card-features">
                            <li><i class="fas fa-check"></i> 10 Signature Wings</li>
                            <li><i class="fas fa-check"></i> 2 Sides of Your Choice</li>
                            <li><i class="fas fa-check"></i> Available Wednesdays Only</li>
                        </ul>
                        <button class="btn customize-btn" data-meal="Wednesday Wing Deal" data-price="12">CUSTOMIZE & ORDER</button>
                    </div>
                </div>
                
                <div class="meal-card">
                    <div class="card-image" style="background-image: url('https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?ixlib=rb-4.0.1&auto=format&fit=crop&w=800&q=80');"></div>
                    <div class="card-content">
                        <h3>Wednesday Family Deal</h3>
                        <p class="card-price">€21</p>
                        <p>Wednesday special! 20 pieces of our delicious fried chicken with 2 sides. Feed the whole family!</p>
                        <ul class="card-features">
                            <li><i class="fas fa-check"></i> 20 Pieces Chicken</li>
                            <li><i class="fas fa-check"></i> 2 Large Sides</li>
                            <li><i class="fas fa-check"></i> Available Wednesdays Only</li>
                        </ul>
                        <button class="btn customize-btn" data-meal="Wednesday Family Deal" data-price="21">CUSTOMIZE & ORDER</button>
                    </div>
                </div>
                
                <div class="meal-card">
                    <div class="card-image" style="background-image: url('https://images.unsplash.com/photo-1608039755401-742074f0548d?ixlib=rb-4.0.1&auto=format&fit=crop&w=800&q=80');"></div>
                    <div class="card-content">
                        <h3>Wednesday Mega Deal</h3>
                        <p class="card-price">€30</p>
                        <p>Wednesday special! 30 pieces of our legendary chicken with 3 sides. Perfect for squad nights!</p>
                        <ul class="card-features">
                            <li><i class="fas fa-check"></i> 30 Pieces Chicken</li>
                            <li><i class="fas fa-check"></i> 3 Large Sides</li>
                            <li><i class="fas fa-check"></i> Available Wednesdays Only</li>
                        </ul>
                        <button class="btn customize-btn" data-meal="Wednesday Mega Deal" data-price="30">CUSTOMIZE & ORDER</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Friday Catfish Menu -->
        <div class="menu-category" id="catfish-menu">
            <div class="meal-cards">
                <div class="meal-card meal-card-popular">
                    <div class="card-badge">
                        <div class="badge" style="background-color: var(--color-primary); color: white;">FRIDAY SPECIAL</div>
                    </div>
                    <div class="card-image" style="background-image: url('https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?ixlib=rb-4.0.1&auto=format&fit=crop&w=800&q=80');"></div>
                    <div class="card-content">
                        <h3>1-Piece Catfish Meal</h3>
                        <p class="card-price">€9</p>
                        <p>Friday Catfish Special! One piece of golden fried catfish with hush puppies and 2 sides.</p>
                        <ul class="card-features">
                            <li><i class="fas fa-check"></i> 1 Piece Fried Catfish</li>
                            <li><i class="fas fa-check"></i> Hush Puppies</li>
                            <li><i class="fas fa-check"></i> 2 Sides of Your Choice</li>
                        </ul>
                        <button class="btn customize-btn" data-meal="1-Piece Catfish Meal" data-price="9">CUSTOMIZE & ORDER</button>
                    </div>
                </div>
                
                <div class="meal-card">
                    <div class="card-image" style="background-image: url('https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?ixlib=rb-4.0.1&auto=format&fit=crop&w=800&q=80');"></div>
                    <div class="card-content">
                        <h3>2-Piece Catfish Meal</h3>
                        <p class="card-price">€12</p>
                        <p>Friday Catfish Special! Two pieces of golden fried catfish with hush puppies and 2 sides.</p>
                        <ul class="card-features">
                            <li><i class="fas fa-check"></i> 2 Pieces Fried Catfish</li>
                            <li><i class="fas fa-check"></i> Hush Puppies</li>
                            <li><i class="fas fa-check"></i> 2 Sides of Your Choice</li>
                        </ul>
                        <button class="btn customize-btn" data-meal="2-Piece Catfish Meal" data-price="12">CUSTOMIZE & ORDER</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
