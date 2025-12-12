// api.js - Module d'intégration des API

const URL_PLANE = 'https://whatisthisplane.alwaysdata.net/ajax/planes';
const URL_FAVORITE = 'https://whatisthisplane.alwaysdata.net/ajax/favorites';

// ============================================
// 1. OPENSKY NETWORK API
// ============================================

/**
 * Récupérer tous les avions en temps réel
 */
async function getAllFlights() {
    try {
        const response = await fetch('https://opensky-network.org/api/states/all');
        const data = await response.json();
        return data.states || [];
    } catch (error) {
        console.error('Erreur OpenSky:', error);
        return [];
    }
}

/**
 * Récupérer les avions dans une zone géographique
 * @param {number} lamin - Latitude minimum
 * @param {number} lomin - Longitude minimum
 * @param {number} lamax - Latitude maximum
 * @param {number} lomax - Longitude maximum
 */
async function getFlightsByBoundingBox(lamin, lomin, lamax, lomax) {
    try {
        const url = `https://opensky-network.org/api/states/all?lamin=${lamin}&lomin=${lomin}&lamax=${lamax}&lomax=${lomax}`;
        const response = await fetch(url);
        const data = await response.json();
        return data.states || [];
    } catch (error) {
        console.error('Erreur OpenSky Bounding Box:', error);
        return [];
    }
}

/**
 * Récupérer un avion spécifique par ICAO24
 * @param {string} icao24 - Code ICAO24 de l'avion (ex: "3c6444")
 */
async function getFlightByIcao24(icao24) {
    try {
        const url = `https://opensky-network.org/api/states/all?icao24=${icao24.toLowerCase()}`;
        const response = await fetch(url);
        const data = await response.json();
        return data.states && data.states.length > 0 ? data.states[0] : null;
    } catch (error) {
        console.error('Erreur OpenSky ICAO24:', error);
        return null;
    }
}

/**
 * Parser les données OpenSky
 * Format du tableau states:
 * [0] icao24, [1] callsign, [2] origin_country, [3] time_position,
 * [4] last_contact, [5] longitude, [6] latitude, [7] baro_altitude,
 * [8] on_ground, [9] velocity, [10] true_track, [11] vertical_rate
 */
function parseOpenSkyData(state) {
    return {
        icao24: state[0],
        callsign: state[1]?.trim() || 'N/A',
        origin_country: state[2],
        time_position: state[3],
        last_contact: state[4],
        longitude: state[5],
        latitude: state[6],
        baro_altitude: state[7],
        on_ground: state[8],
        velocity: state[9],
        true_track: state[10],
        vertical_rate: state[11]
    };
}

// ============================================
// 2. ADSBDB API
// ============================================

/**
 * Obtenir les infos d'un vol via ADSBdb par callsign
 * @param {string} callsign - ex: AFR45
 */
async function getFlightRouteByCallsign(callsign) {
    if (!callsign || callsign.trim() === "") {
        console.warn("Aucun callsign fourni");
        return null;
    }

    // Nettoyage du callsign (enlever espaces)
    const cleanCallsign = callsign.trim();

    const url = `https://api.adsbdb.com/v0/callsign/${cleanCallsign}`;

    try {
        const response = await fetch(url);
        const data = await response.json();

        if (!data.response || !data.response.flightroute) {
            return null;
        }

        const r = data.response.flightroute;

        return {
            callsign: r.callsign || cleanCallsign,

            airline: r.airline?.name || "Inconnu",
            airline_icao: r.airline?.icao || null,
            airline_iata: r.airline?.iata || null,
            airline_callsign: r.airline?.callsign || null,

            origin: r.origin ? {
                iata: r.origin.iata_code,
                icao: r.origin.icao_code,
                name: r.origin.name,
                city: r.origin.municipality,
                country: r.origin.country_name,
                lat: r.origin.latitude,
                lon: r.origin.longitude
            } : null,

            destination: r.destination ? {
                iata: r.destination.iata_code,
                icao: r.destination.icao_code,
                name: r.destination.name,
                city: r.destination.municipality,
                country: r.destination.country_name,
                lat: r.destination.latitude,
                lon: r.destination.longitude
            } : null
        };

    } catch (error) {
        console.error("Erreur ADSBDB flight route API :", error);
        return null;
    }
}

/**
 * Obtenir toutes les infos d'un avion 
 * @param {string} icao (mode-S) - ex: AD6BDC
 */
async function getAircraftData(icao) {
    if (!icao || icao.trim() === "") {
        console.warn("Aucun icao fourni");
        return null;
    }

    // Nettoyage icao (enlever espaces)
    const cleanicao = icao.trim();

    const url = `https://api.adsbdb.com/v0/aircraft/${cleanicao}`;

    try {
        const response = await fetch(url);
        const data = await response.json();

        if (!data.response || !data.response.aircraft) {
            return null;
        }

        // Retourne toutes les données de l’avion
        return data.response.aircraft;

    } catch (error) {
        console.error("Erreur ADSBDB Aircraft API :", error);
        return null;
    }
}

/**
 * Obtenir les infos l'image d'un avion 
 * @param {string} icao (mode-S) - ex: AD6BDC
 */
async function getAircraftPhotos(icao) {
    const data = await getAircraftData(icao);
    if (!data) return null;

    return {
        url_photo: data.url_photo || null,
        url_photo_thumbnail: data.url_photo_thumbnail || null
    };
}

/**
 * Obtenir les infos du nom complet d'un avion 
 * @param {string} icao (mode-S) - ex: AD6BDC
 */
async function getAircraftName(icao) {
    const data = await getAircraftData(icao);
    if (!data) return null;

    const manufacturer = data.manufacturer || "";
    const type = data.type || "";

    const fullName = `${manufacturer} ${type}`.trim();
    return fullName || null;
}

// ============================================
// 3. BigDataCloudLocation (Géolocalisation)
// ============================================
async function getBigDataCloudLocation() {
    try {
        const response = await fetch('https://api.bigdatacloud.net/data/reverse-geocode-client');
        const data = await response.json();
        
        console.log('BigDataCloud:', data);
        
        return {
            lat: data.latitude,
            lon: data.longitude,
            city: data.city || data.locality || 'Inconnu',
            postcode: data.postcode || '',
            suburb: data.localityInfo?.administrative?.[3]?.name || '',
            country: data.countryName || 'France',
            full_address: `${data.locality}, ${data.principalSubdivision}, ${data.countryName}`,
            method: 'BigDataCloud IP'
        };
    } catch (error) {
        console.error('BigDataCloud échoué:', error);
        throw error;
    }
}

/**
 * Récupérer les avions proches d'une position
 * @param {number} radius - rayon en degrés (~1° = 111 km)
 * @param {number} lat - latitude centrale (optionnel)
 * @param {number} lon - longitude centrale (optionnel)
 */
async function getNearbyFlights(radius = 1, lat = null, lon = null) {
    // Si pas de position fournie, récupérer celle de l'utilisateur
    if (lat === null || lon === null) {
        const pos = await getBigDataCloudLocation();
        if (!pos) return [];
        lat = pos.lat;
        lon = pos.lon;
    }

    const lamin = lat - radius;
    const lamax = lat + radius;
    const lomin = lon - radius;
    const lomax = lon + radius;

    try {
        const rawStates = await getFlightsByBoundingBox(lamin, lomin, lamax, lomax);
        // parser les données pour correspondre à ton code
        return rawStates
            .map(parseOpenSkyData)
            .filter(f => f.latitude && f.longitude);
    } catch (e) {
        console.error('Erreur getNearbyFlights:', e);
        return [];
    }
}

/**
 * Ajouter un avion aux favoris
 */
async function addToFavorites(planeData) {
    try {
        const response = await fetch(`${URL_FAVORITE}/add_favorite.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                icao24: planeData.icao24,
                callsign: planeData.callsign,
                aircraft_model: planeData.model,
                airline: planeData.airline,
                origin_iata: planeData.origin_iata || null,
                origin_name: planeData.origin_name || null,
                destination_iata: planeData.destination_iata || null,
                destination_name: planeData.destination_name || null
            })
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur ajout favori:', error);
        return { error: 'Erreur serveur' };
    }
}

/**
 * Retirer un avion des favoris
 */
async function removeFromFavorites(icao24) {
    try {
        const response = await fetch(`${URL_FAVORITE}/remove_favorite.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({icao24})
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur suppression favori:', error);
        return { error: 'Erreur serveur' };
    }
}

/**
 * Récupérer les favoris d'un utilisateur
 */
async function getUserFavorites() {
    try {
        const response = await fetch(`${URL_FAVORITE}/get_favorite.php`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erreur récupération favoris:', error);
        return [];
    }
}

/**
 * Rechercher un avion dans la base de données
 */
async function searchAirplaneInDB(icao24) {
    try {
        const response = await fetch(`${URL_PLANE}/search_airplane.php?icao24=${icao24}`);
        return await response.json();
    } catch (error) {
        console.error('Erreur recherche avion:', error);
        return { error: 'Erreur serveur' };
    }
}
