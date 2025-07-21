// Password for the sake of demonstration
const DEMO_PASSWORD = "admin123";

function promptLogin() {
  const pwd = prompt("Enter admin password:");
  if (pwd !== DEMO_PASSWORD) {
    alert("Incorrect password.");
    window.location.href = "index.html";
  }
}

promptLogin();

// Fetch stats from Flask backend
fetch('http://127.0.0.1:5000/api/stats')
  .then(res => res.json())
  .then(data => {
    const { totalTests, fakeReceipts, realReceipts } = data;
    const ratio = realReceipts > 0 ? (fakeReceipts / realReceipts).toFixed(2) : "N/A";

    document.getElementById('total-tests').textContent = totalTests;
    document.getElementById('fake-count').textContent = fakeReceipts;
    document.getElementById('real-count').textContent = realReceipts;
    document.getElementById('ratio').textContent = ratio;

    // Draw the chart
    const ctx = document.getElementById('statsChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Total Tests', 'Fake Receipts', 'Real Receipts'],
        datasets: [{
          label: 'Count',
          data: [totalTests, fakeReceipts, realReceipts],
          backgroundColor: [
            'rgba(220, 20, 60, 0.7)',
            'rgba(255, 99, 132, 0.7)',
            'rgba(60, 179, 113, 0.7)'
          ],
          borderColor: [
            'rgba(220, 20, 60, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(60, 179, 113, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { color: '#fff' }, grid: { color: '#333' } },
          x: { ticks: { color: '#fff' }, grid: { color: '#333' } }
        }
      }
    });
  });