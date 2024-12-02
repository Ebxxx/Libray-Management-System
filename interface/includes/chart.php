    <!-- Category Distribution Chart (Hidden for Non-Admin) -->
    <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Book Categories Distribution</h5>
                            <canvas id="bookCategoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>