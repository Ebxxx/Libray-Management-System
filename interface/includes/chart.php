<?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
            <!-- Add chart section -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-start mb-1">
                                <select id="yearSelector" class="form-select" style="width: auto;">
                                    <?php
                                    $currentYear = date('Y');
                                    for($year = $currentYear; $year >= $currentYear - 4; $year--) {
                                        echo "<option value='$year'>$year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <canvas id="monthlyBorrowingsChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <canvas id="resourceDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>