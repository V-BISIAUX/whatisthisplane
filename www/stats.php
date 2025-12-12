<?php
    declare(strict_types=1);
    if (session_status() === PHP_SESSION_NONE && isset($_COOKIE[session_name()])) {
        session_start();
    }

    $description = "Découvrez les avions les plus recherchés sur What Is This Plane, Visualisez les modéls d'avions les plus recherchés";
    $title = "What is this plane - Statistiques des avions les plus recherchés.";
    require "../src/includes/header.inc.php";
?>

    <main>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <h1>Statistiques des avions les plus consultées</h1>
        <section>
            <h2>Analyse des Données</h2>
            <article id="histogramme">
                <h3>Histogramme des Consultations</h3>
                <p>Ci-dessous, un histogramme représentant les avions les plus consultées sur notre site.</p>
                <figure>
                    <canvas id="mnDiagramme" width="900" height="700"></canvas>
                    <figcaption>Histogramme des avions les plus consultées du site</figcaption>
                </figure>
            </article>
        </section>
            <script>
                async function initChart() {
                    const response = await fetch('ajax/user/data_stats.php');
                    const data = await response.json();
                    const labels = data.map(item => item.aircraft_model);
                    const values = data.map(item => item.nombre);
                    const histogramme = document.getElementById('mnDiagramme').getContext('2d');
                    new Chart(histogramme, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: "Nombre de visite",
                                data: values,
                                backgroundColor: 'rgba(75, 192, 192, 0.6)', // couleur des barres
                                borderColor: 'rgba(75, 192, 192, 1)', // bordures
                                borderWidth: 1,
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: { display: true, text: "Nombre de visites" },
                                },
                                x: {
                                    title: { display: true, text: "Noms des avions" },
                                },
                            },
                        },
                    });
                }

                initChart();
            </script>
    </main>

<?php
    require "../src/includes/footer.inc.php";
?>