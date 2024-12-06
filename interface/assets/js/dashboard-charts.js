document.addEventListener('DOMContentLoaded', function() {
    // Resource Distribution Chart
    var ctx = document.getElementById('resourceDistributionChart').getContext('2d');
    var resourceData = {
        labels: ['Books', 'Media Resources', 'Periodicals'],
        datasets: [{
            data: [
                initialResourceData.books,
                initialResourceData.mediaResources,
                initialResourceData.periodicals
            ],
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)'
            ]
        }]
    };
    
    new Chart(ctx, {
        type: 'pie',
        data: resourceData,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.5,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Resource Type Distribution'
                }
            }
        }
    });
});

// Monthly Borrowings Chart
let monthlyBorrowingsChart; // Declare chart variable globally

function initializeMonthlyChart(data) {
    var monthlyCtx = document.getElementById('monthlyBorrowingsChart').getContext('2d');
    var monthlyData = {
        labels: ['January', 'February', 'March', 'April', 'May', 'June', 
                'July', 'August', 'September', 'October', 'November', 'December'],
        datasets: [{
            label: 'Number of Borrowings',
            data: data,
            backgroundColor: 'rgba(75, 192, 192, 0.8)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    };
    
    if (monthlyBorrowingsChart) {
        monthlyBorrowingsChart.destroy();
    }
    
    monthlyBorrowingsChart = new Chart(monthlyCtx, {
        type: 'bar',
        data: monthlyData,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.5,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },  
                title: {
                    display: true,
                    text: 'Monthly Borrowings for ' + document.getElementById('yearSelector').value
                }
            }
        }
    });
}

// Initialize chart with current year's data
initializeMonthlyChart(initialMonthlyData);

// Add event listener for year selection
document.getElementById('yearSelector').addEventListener('change', function() {
    fetch(`../controller/get_monthly_borrowings.php?year=${this.value}`)
        .then(response => response.json())
        .then(data => {
            initializeMonthlyChart(data);
        })
        .catch(error => console.error('Error:', error));
});