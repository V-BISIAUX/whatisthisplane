/**
 * Affiche les résultats de recherche AviationStack
 * Gère tous les types : flight_number, airport, airline
 */
function renderAviationStackResults(data) {
    let output = '';

    // En-tête avec type de recherche
    output += `<div class="search-info">`;
    output += `<p><strong>${getSearchTypeLabel(data.type)}</strong></p>`;
    if (data.note) {
        output += `<p class="search-note"><em>${data.note}</em></p>`;
    }
    output += `</div>`;

    // Affichage selon le type
    switch(data.type) {
        case 'flight_number':
        case 'airline':
            return output + renderFlightList(data.results);
        
        case 'airport':
            return output + renderAirportResults(data);
        
        default:
            return '<p class="no-results">Type de résultat non supporté</p>';
    }
}

/**
 * Label du type de recherche
 */
function getSearchTypeLabel(type) {
    const labels = {
        'flight_number': 'Numéro de vol',
        'airport': 'Aéroport',
        'airline': 'Compagnie aérienne'
    };
    return labels[type] || 'Recherche';
}

/**
 * Affiche une liste de vols
 */
function renderFlightList(flights) {
    if (!flights || flights.length === 0) {
        return '<p class="no-results">Aucun vol trouvé</p>';
    }

    let output = '<div class="flights-list">';

    flights.forEach(flight => {
        output += `
            <article class="flight-card">
                <!-- En-tête -->
                <div class="flight-header">
                    <h4>
						${flight.flight_iata || 'N/A'}
						<br>
						<small style="color:#666; font-size:0.8em;">
							${flight.flight_number || '—'}
						</small>
					</h4>
                    <span class="badge badge-${flight.status}">${getStatusLabel(flight.status)}</span>
                </div>

                <!-- Compagnie -->
                <div class="flight-airline">
                    ${flight.airline?.name || 'Compagnie inconnue'}
                    ${flight.airline?.iata ? `<span class="code">(${flight.airline.iata})</span>` : ''}
                </div>

                <!-- Route -->
                <div class="flight-route">
                    <div class="airport">
                        <div class="airport-code">${flight.departure?.iata || '---'}</div>
                        <div class="airport-name">${flight.departure?.airport || 'Départ', 25}</div>
                        ${formatSchedule(flight.departure)}
                        ${formatTerminalGate(flight.departure)}
                    </div>

                    <div class="arrow">→</div>

                    <div class="airport">
                        <div class="airport-code">${flight.arrival?.iata || '---'}</div>
                        <div class="airport-name">${flight.arrival?.airport || 'Arrivée', 25}</div>
                        ${formatSchedule(flight.arrival)}
                        ${formatTerminalGate(flight.arrival)}
                    </div>
                </div>

                <!-- Avion -->
                <div class="flight-aircraft">
					<strong>Avion</strong>
					<div class="aircraft-grid">
						<span>Immat: ${flight.aircraft?.registration || 'Inconnue'}</span>
						<span>Type: ${flight.aircraft?.iata || 'Non communiqué'}</span>
					</div>
				</div>

            </article>
        `;
    });

    output += '</div>';
    return output;
}

/**
 * Affiche les résultats pour un aéroport
 */
function renderAirportResults(data) {
	const MAX_FLIGHTS_DISPLAY = 10;
    let output = `<div class="airport-results">`;
    output += `<h3>Aéroport ${data.airport_code}</h3>`;
	
    // Départs
    if (data.departures && data.departures.length > 0) {
		
		const total = data.departures.length;
        const displayed = Math.min(total, MAX_FLIGHTS_DISPLAY);
		
        output += `
            <div class="airport-section">
                <h4 class="section-title">Départs (${total} trouvés, affichage ${displayed})</h4>
                ${renderFlightList(data.departures.slice(0, MAX_FLIGHTS_DISPLAY))}
            </div>
        `;
    } else {
        output += `<p class="no-results">Aucun départ trouvé</p>`;
    }

    // Arrivées
    if (data.arrivals && data.arrivals.length > 0) {
		
		const total = data.departures.length;
        const displayed = Math.min(total, MAX_FLIGHTS_DISPLAY);
		
        output += `
            <div class="airport-section">
                <h4 class="section-title">Arrivées (${total} trouvés, affichage ${displayed})</h4>
                ${renderFlightList(data.arrivals.slice(0, MAX_FLIGHTS_DISPLAY))}
            </div>
        `;
    } else {
        output += `<p class="no-results">Aucune arrivée trouvée</p>`;
    }

    output += `</div>`;
    return output;
}

/**
 * Formate les horaires
 */
function formatSchedule(location) {
    if (!location) return '';
    
    let html = '';
    
    if (location.scheduled) {
        html += `<div class="time">${formatTime(location.scheduled)}</div>`;
    }
    
    if (location.actual) {
        html += `<div class="time actual">${formatTime(location.actual)}</div>`;
    } else if (location.estimated && location.estimated !== location.scheduled) {
        html += `<div class="time estimated">${formatTime(location.estimated)}</div>`;
    }
    
    return html;
}

/**
 * Formate terminal et porte
 */
function formatTerminalGate(location) {
    if (!location) return '';
    
    let html = '';
    
    if (location.terminal) {
        html += `<span class="tag">T${location.terminal}</span>`;
    }
    
    if (location.gate) {
        html += `<span class="tag">Porte ${location.gate}</span>`;
    }
    
    return html ? `<div class="tags">${html}</div>` : '';
}

/**
 * Formate une date/heure ISO
 */
function formatTime(isoString) {
    if (!isoString) return 'N/A';
    
    try {
        const date = new Date(isoString);
        return date.toLocaleString('fr-FR', { 
            hour: '2-digit', 
            minute: '2-digit',
            day: '2-digit',
            month: '2-digit'
        });
    } catch (e) {
        return isoString.substring(11, 16);
    }
}

/**
 * Label du statut
 */
function getStatusLabel(status) {
    const labels = {
        'scheduled': 'Programmé',
        'active': 'En vol',
        'landed': 'Atterri',
        'cancelled': 'Annulé',
        'incident': 'Incident',
        'diverted': 'Dérouté'
    };
    return labels[status] || status || 'Inconnu';
}