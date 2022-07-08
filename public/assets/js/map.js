let baseUrl = '';
let currentUrl = '';
let currentLat = 0, currentLng = 0
let userLat = 0, userLng = 0;
let map;
let infoWindow = new google.maps.InfoWindow();
let userInfoWindow = new google.maps.InfoWindow();
let directionsService, directionsRenderer;
let userMarker = new google.maps.Marker();
let destinationMarker = new google.maps.Marker();
let routeArray = [], circleArray = [], markerArray = {};
let bounds = new google.maps.LatLngBounds();

function setBaseUrl(url) {
    baseUrl = url;
}

// Initialize and add the map
function initMap(lat = -0.5242972, lng = 100.492333) {
    directionsService = new google.maps.DirectionsService();
    const center = new google.maps.LatLng(lat, lng);
    map = new google.maps.Map(document.getElementById("googlemaps"), {
        zoom: 18,
        center: center,
        mapTypeId: 'roadmap',
    });
    var rendererOptions = {
        map: map
    }
    directionsRenderer = new google.maps.DirectionsRenderer(rendererOptions);
    digitVillage();
}

// Display tourism village digitizing
function digitVillage() {
    const village = new google.maps.Data();
    $.ajax({
        url: baseUrl + '/api/village',
        type: 'POST',
        data: {
            village: 'VIL02'
        },
        dataType: 'json',
        success: function (response) {
            const data = response.data;
            village.addGeoJson(data);
            village.setStyle({
                fillColor:'#00b300',
                strokeWeight:0.5,
                strokeColor:'#ffffff',
                fillOpacity: 0.1,
            });
            village.setMap(map);
        }
    });
}

// Remove user location
function clearUser() {
    userLat = 0;
    userLng = 0;
    userMarker.setMap(null);
}

// Set current location based on user location
function setUserLoc(lat, lng) {
    userLat = lat;
    userLng = lng;
    currentLat = userLat;
    currentLng = userLng;
}

// Remove any route shown
function clearRoute() {
    for(i in routeArray) {
        routeArray[i].setMap(null);
    }
    routeArray = [];
    $('#direction-row').hide();
}

// Remove any radius shown
function clearRadius() {
    for (i in circleArray) {
        circleArray[i].setMap(null);
    }
    circleArray = [];
}

// Remove any marker shown
function clearMarker() {
    for (i in markerArray) {
        markerArray[i].setMap(null);
    }
    markerArray = {};
}

// Get user's current position
function currentPosition() {
    clearRadius();
    clearRoute();

    google.maps.event.clearListeners(map, 'click');
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
                const pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                };

                infoWindow.close();
                clearUser();
                markerOption = {
                    position: pos,
                    animation: google.maps.Animation.DROP,
                    map: map,
                };
                userMarker.setOptions(markerOption);
                userInfoWindow.setContent("<p class='text-center'><span class='fw-bold'>You are here.</span> <br> lat: " + pos.lat + "<br>long: " + pos.lng+ "</p>");
                userInfoWindow.open(map, userMarker);
                map.setCenter(pos);
                setUserLoc(pos.lat, pos.lng);

                userMarker.addListener('click', () => {
                    userInfoWindow.open(map, userMarker);
                });
            },
            () => {
                handleLocationError(true, userInfoWindow, map.getCenter());
            }
        );
    } else {
        // Browser doesn't support Geolocation
        handleLocationError(false, userInfoWindow, map.getCenter());
    }
}

// Error handler for geolocation
function handleLocationError(browserHasGeolocation, infoWindow, pos) {
    infoWindow.setPosition(pos);
    infoWindow.setContent(
        browserHasGeolocation
            ? "Error: The Geolocation service failed."
            : "Error: Your browser doesn't support geolocation."
    );
    infoWindow.open(map);
}

// User set position on map
function manualPosition() {

    clearRadius();
    clearRoute();

    if (userLat == 0 && userLng == 0) {
        Swal.fire('Click on Map');
    }
    map.addListener('click', (mapsMouseEvent) => {

        infoWindow.close();
        pos = mapsMouseEvent.latLng;

        clearUser();
        markerOption = {
            position: pos,
            animation: google.maps.Animation.DROP,
            map: map,
        };
        userMarker.setOptions(markerOption);
        userInfoWindow.setContent("<p class='text-center'><span class='fw-bold'>You are here.</span> <br> lat: " + pos.lat().toFixed(8) + "<br>long: " + pos.lng().toFixed(8)+ "</p>");
        userInfoWindow.open(map, userMarker);

        userMarker.addListener('click', () => {
            userInfoWindow.open(map, userMarker);
        });

        setUserLoc(pos.lat().toFixed(8), pos.lng().toFixed(8))
    });

}

// Render route on selected object
function routeTo(lat, lng, routeFromUser = true) {

    clearRadius();
    clearRoute();
    google.maps.event.clearListeners(map, 'click')

    let start, end;
    if (routeFromUser) {
        if (userLat == 0 && userLng == 0) {
            return Swal.fire('Determine your position first!');
        }
        setUserLoc(userLat, userLng);
    }
    start = new google.maps.LatLng(currentLat, currentLng);
    end = new google.maps.LatLng(lat, lng)
    let request = {
        origin: start,
        destination: end,
        travelMode: 'DRIVING'
    };
    directionsService.route(request, function(result, status) {
        if (status == 'OK') {
            directionsRenderer.setDirections(result);
            showSteps(result);
            directionsRenderer.setMap(map);
            routeArray.push(directionsRenderer);
        }
    });
    boundToRoute(start, end);
}

// Display marker for loaded object
function objectMarker(id, lat, lng, anim = true) {

    google.maps.event.clearListeners(map, 'click');
    let pos = new google.maps.LatLng(lat, lng);
    let marker = new google.maps.Marker();

    let icon;
    if (id.substring(0,2) === "RG") {
        icon = baseUrl + '/media/icon/marker_rg.png';
    } else if (id.substring(0,2) === "CP") {
        icon = baseUrl + '/media/icon/marker_cp.png';
    } else if (id.substring(0,2) === "WP") {
        icon = baseUrl + '/media/icon/marker_wp.png';
    } else if (id.substring(0,2) === "SP") {
        icon = baseUrl + '/media/icon/marker_sp.png';
    } else if (id.substring(0,2) === "EV") {
        icon = baseUrl + '/media/icon/marker_ev.png';
    }

    markerOption = {
        position: pos,
        icon: icon,
        animation: google.maps.Animation.DROP,
        map: map,
    }
    marker.setOptions(markerOption);
    if (!anim) {
        marker.setAnimation(null);
    }
    marker.addListener('click', () => {
        infoWindow.close();
        objectInfoWindow(id);
        infoWindow.open(map, marker);
    });
    markerArray[id] = marker;
}

// Display info window for loaded object
function objectInfoWindow(id){
    let content = '';
    let contentButton = '';

    if (id.substring(0,2) === "RG") {
        $.ajax({
            url: baseUrl + '/api/rumahGadang/' + id,
            dataType: 'json',
            success: function (response) {
                let data = response.data;
                let rgid = data.id;
                let name = data.name;
                let lat = data.lat;
                let lng = data.lng;
                let ticket_price = (data.ticket_price == 0) ? 'Free' : 'Rp ' + data.ticket_price;
                let open = data.open.substring(0, data.open.length - 3);
                let close = data.close.substring(0, data.close.length - 3);

                content =
                    '<div class="text-center">' +
                    '<p class="fw-bold fs-6">'+ name +'</p> <br>' +
                    '<p><i class="fa-solid fa-clock"></i> '+open+' - '+close+' WIB</p>' +
                    '<p><i class="fa-solid fa-money-bill"></i> '+ ticket_price +'</p>' +
                    '</div><br>';
                contentButton =
                    '<div class="text-center">' +
                    '<a title="Route" class="btn icon btn-outline-primary mx-1" id="routeInfoWindow" onclick="routeTo('+lat+', '+lng+')"><i class="fa-solid fa-road"></i></a>' +
                    '<a title="Info" class="btn icon btn-outline-primary mx-1" target="_blank" id="infoInfoWindow" href='+baseUrl+'/web/rumahGadang/'+rgid+'><i class="fa-solid fa-info"></i></a>' +
                    '<a title="Nearby" class="btn icon btn-outline-primary mx-1" id="nearbyInfoWindow" onclick="openNearby(`'+ rgid +'`,'+ lat +','+ lng +')"><i class="fa-solid fa-compass"></i></a>' +
                    '</div>'

                if (currentUrl.substring(currentUrl.length - 5) === id) {
                    infoWindow.setContent(content);
                    infoWindow.open(map, markerArray[rgid])
                } else {
                    infoWindow.setContent(content + contentButton);
                }
            }
        });
    } else if (id.substring(0,2) === "EV") {
        $.ajax({
            url: baseUrl + '/api/event/' + id,
            dataType: 'json',
            success: function (response) {
                let data = response.data;
                let evid = data.id;
                let name = data.name;
                let lat = data.lat;
                let lng = data.lng;
                let ticket_price = (data.ticket_price == 0) ? 'Free' : 'Rp ' + data.ticket_price;
                let category = data.category;
                let date_start = new Date(data.date_start);
                let date_end = new Date(data.date_end);
                let start = date_start.getDate() + '/' + (date_start.getMonth() + 1) + '/' + date_start.getFullYear();
                let end = date_end.getDate() + '/' + (date_end.getMonth() + 1) + '/' + date_end.getFullYear();

                content =
                    '<div class="text-center">' +
                    '<p class="fw-bold fs-6">'+ name +'</p> <br>' +
                    '<p><i class="fa-solid fa-layer-group"></i> '+ category +'</p>' +
                    '<p><i class="fa-solid fa-money-bill"></i> '+ ticket_price +'</p>' +
                    '<p><i class="fa-solid fa-calendar-days"></i> '+ start +' - '+ end +'</p>' +
                    '</div><br>';
                contentButton =
                    '<div class="text-center">' +
                    '<a title="Route" class="btn icon btn-outline-primary mx-1" id="routeInfoWindow" onclick="routeTo('+lat+', '+lng+')"><i class="fa-solid fa-road"></i></a>' +
                    '<a title="Info" class="btn icon btn-outline-primary mx-1" target="_blank" id="infoInfoWindow" href='+baseUrl+'/web/event/'+evid+'><i class="fa-solid fa-info"></i></a>' +
                    '<a title="Nearby" class="btn icon btn-outline-primary mx-1" id="nearbyInfoWindow" onclick="openNearby(`'+ evid +'`,'+ lat +','+ lng +')"><i class="fa-solid fa-compass"></i></a>' +
                    '</div>'

                if (currentUrl.substring(currentUrl.length - 5) === id) {
                    infoWindow.setContent(content);
                    infoWindow.open(map, markerArray[evid])
                } else {
                    infoWindow.setContent(content + contentButton);
                }
            }
        });
    } else if (id.substring(0,2) === "CP") {
        $.ajax({
            url: baseUrl + '/api/culinaryPlace/' + id,
            dataType: 'json',
            success: function (response) {
                let data = response.data;
                let name = data.name;

                content =
                    '<div class="text-center">' +
                    '<p class="fw-bold fs-6">'+ name +'</p>' +
                    '</div>';

                infoWindow.setContent(content);
            }
        });
    } else if (id.substring(0,2) === "WP") {
        $.ajax({
            url: baseUrl + '/api/worshipPlace/' + id,
            dataType: 'json',
            success: function (response) {
                let data = response.data;
                let name = data.name;

                content =
                    '<div class="text-center">' +
                    '<p class="fw-bold fs-6">'+ name +'</p>' +
                    '</div>';

                infoWindow.setContent(content);
            }
        });
    } else if (id.substring(0,2) === "SP") {
        $.ajax({
            url: baseUrl + '/api/souvenirPlace/' + id,
            dataType: 'json',
            success: function (response) {
                let data = response.data;
                let name = data.name;

                content =
                    '<div class="text-center">' +
                    '<p class="fw-bold fs-6">'+ name +'</p>' +
                    '</div>';

                infoWindow.setContent(content);
            }
        });
    }
}

// Render map to contains all object marker
function boundToObject() {
    if (Object.keys(markerArray).length > 0) {
        bounds = new google.maps.LatLngBounds();
        for (i in markerArray) {
            bounds.extend(markerArray[i].getPosition())
        }
        map.fitBounds(bounds, 80);
    } else {
        let pos = new google.maps.LatLng(-0.5242972, 100.492333);
        map.panTo(pos);
    }
}

// Render map to contains route and its markers
function boundToRoute(start, end) {
    bounds = new google.maps.LatLngBounds();
    bounds.extend(start);
    bounds.extend(end);
    map.panToBounds(bounds, 100);
}

// Add user position to map bound
function boundToRadius(lat, lng, rad) {
    let userBound = new google.maps.LatLng(lat, lng);
    const radiusCircle = new google.maps.Circle({
        center: userBound,
        radius: Number(rad)
    });
    map.fitBounds(radiusCircle.getBounds());
}

// Draw radius circle
function drawRadius(position, radius) {
    const radiusCircle = new google.maps.Circle({
        center: position,
        radius: radius,
        map: map,
        strokeColor: "#FF0000",
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: "#FF0000",
        fillOpacity: 0.35,
    });
    circleArray.push(radiusCircle);
    boundToRadius(currentLat, currentLng, radius);
}

// Update radiusValue on search by radius
function updateRadius(postfix) {
    document.getElementById('radiusValue' + postfix).innerHTML = (document.getElementById('inputRadius' + postfix).value * 100) + " m";
}

// Render search by radius
function radiusSearch({postfix= null, } = {}) {

    if (userLat == 0 && userLng == 0) {
        document.getElementById('radiusValue' + postfix).innerHTML = "0 m";
        document.getElementById('inputRadius' + postfix).value = 0;
        return Swal.fire('Determine your position first!');
    }

    clearRadius();
    clearRoute();
    clearMarker();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click');
    $('#direction-row').hide();
    $('#check-nearby-col').hide();
    $('#result-nearby-col').hide();
    $('#list-rg-col').show();
    $('#list-ev-col').show();

    let pos = new google.maps.LatLng(currentLat, currentLng);
    let radiusValue = parseFloat(document.getElementById('inputRadius' + postfix).value) * 100;
    map.panTo(pos);

    // find object in radius
    if (postfix === 'RG') {
        $.ajax({
            url: baseUrl + '/api/rumahGadang/findByRadius',
            type: 'POST',
            data: {
                lat: currentLat,
                long: currentLng,
                radius: radiusValue
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                drawRadius(pos, radiusValue);
            }
        });
    } else if (postfix === 'EV') {
        $.ajax({
            url: baseUrl + '/api/event/findByRadius',
            type: 'POST',
            data: {
                lat: currentLat,
                long: currentLng,
                radius: radiusValue
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                drawRadius(pos, radiusValue);
            }
        });
    }

}

// pan to selected object
function focusObject(id) {
    google.maps.event.trigger(markerArray[id], 'click');
    map.panTo(markerArray[id].getPosition())
}

// display objects by feature used
function displayFoundObject(response) {
    $('#table-data').empty();
    let data = response.data;
    let counter = 1;
    for (i in data) {
        let item = data[i];
        let row =
            '<tr>'+
            '<td>'+ counter +'</td>' +
            '<td class="fw-bold">'+ item.name +'</td>' +
            '<td>'+
            '<a data-bs-toggle="tooltip" data-bs-placement="bottom" title="More Info" class="btn icon btn-primary mx-1" onclick="focusObject(`'+ item.id +'`);">' +
            '<span class="material-symbols-outlined">info</span>' +
            '</a>' +
            '</td>'+
            '</tr>';
        $('#table-data').append(row);
        objectMarker(item.id, item.lat, item.lng);
        counter++;
    }
}

// display steps of direction to selected route
function showSteps(directionResult) {
    $('#direction-row').show();
    $('#table-direction').empty();
    let myRoute = directionResult.routes[0].legs[0];
    for (let i = 0; i < myRoute.steps.length; i++) {
        let distance = myRoute.steps[i].distance.value;
        let instruction = myRoute.steps[i].instructions;
        let row =
            '<tr>' +
            '<td>'+ distance.toLocaleString("id-ID") +'</td>' +
            '<td>'+ instruction +'</td>' +
            '</tr>';
        $('#table-direction').append(row);
    }
}

// open nearby search section
function openNearby(id, lat, lng) {
    $('#list-rg-col').hide();
    $('#list-ev-col').hide();
    $('#list-rec-col').hide();
    $('#check-nearby-col').show();

    currentLat = lat;
    currentLng = lng;
    let pos = new google.maps.LatLng(currentLat, currentLng);
    map.panTo(pos);

    document.getElementById('inputRadiusNearby').setAttribute('onchange', 'updateRadius("Nearby"); checkNearby("'+id+'")');
}

// Search Result Object Around
function checkNearby(id) {
    clearRadius();
    clearRoute();
    clearMarker();
    clearUser();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click')

    objectMarker(id, currentLat, currentLng, false);

    $('#table-cp').empty();
    $('#table-wp').empty();
    $('#table-sp').empty();
    $('#table-cp').hide();
    $('#table-wp').hide();
    $('#table-sp').hide();

    let radiusValue = parseFloat(document.getElementById('inputRadiusNearby').value) * 100;
    const checkCP = document.getElementById('check-cp').checked;
    const checkWP = document.getElementById('check-wp').checked;
    const checkSP = document.getElementById('check-sp').checked;

    if (!checkCP && !checkWP && !checkSP) {
        document.getElementById('radiusValueNearby').innerHTML = "0 m";
        document.getElementById('inputRadiusNearby').value = 0;
        return Swal.fire('Please choose one object');
    }

    if (checkCP) {
        findNearby('cp', radiusValue);
        $('#table-cp').show();
    }
    if (checkWP) {
        findNearby('wp', radiusValue);
        $('#table-wp').show();
    }
    if (checkSP) {
        findNearby('sp', radiusValue);
        $('#table-sp').show();
    }
    drawRadius(new google.maps.LatLng(currentLat, currentLng), radiusValue);
    $('#result-nearby-col').show();
}

// Fetch object nearby by category
function findNearby(category, radius) {
    let pos = new google.maps.LatLng(currentLat, currentLng);
    if (category === 'cp') {
        $.ajax({
            url: baseUrl + '/api/culinaryPlace/findByRadius',
            type: 'POST',
            data: {
                lat: currentLat,
                long: currentLng,
                radius: radius
            },
            dataType: 'json',
            success: function (response) {
                displayNearbyResult(category, response);
            }
        });
    } else if (category === 'wp') {
        $.ajax({
            url: baseUrl + '/api/worshipPlace/findByRadius',
            type: 'POST',
            data: {
                lat: currentLat,
                long: currentLng,
                radius: radius
            },
            dataType: 'json',
            success: function (response) {
                displayNearbyResult(category, response);
            }
        });
    } else if (category === 'sp') {
        $.ajax({
            url: baseUrl + '/api/souvenirPlace/findByRadius',
            type: 'POST',
            data: {
                lat: currentLat,
                long: currentLng,
                radius: radius
            },
            dataType: 'json',
            success: function (response) {
                displayNearbyResult(category, response);
            }
        });
    }
}

// Add nearby object to corresponding table
function displayNearbyResult(category, response) {
    let data = response.data;
    let headerName;
    if (category === 'cp') {
        headerName = 'Culinary';
    } else if (category === 'wp') {
        headerName = 'Worship';
    } else if (category === 'sp') {
        headerName = 'Souvenir'
    }
    let table =
        '<thead><tr>' +
        '<th>'+ headerName +' Name</th>' +
        '<th>Action</th>' +
        '</tr></thead>' +
        '<tbody id="data-'+category+'">' +
        '</tbody>';
    $('#table-'+category).append(table);

    for (i in data) {
        let item = data[i];
        let row =
            '<tr>'+
            '<td class="fw-bold">'+ item.name +'</td>' +
            '<td>'+
            '<a title="Route" class="btn icon btn-primary mx-1" onclick="routeTo('+item.lat+', '+item.lng+', false)"><i class="fa-solid fa-road"></i></a>' +
            '<a title="Info" class="btn icon btn-primary mx-1" onclick="infoModal(`'+ item.id +'`)"><i class="fa-solid fa-info"></i></a>' +
            '<a title="Location" class="btn icon btn-primary mx-1" onclick="focusObject(`'+ item.id +'`);"><i class="fa-solid fa-location-dot"></i></a>' +
            '</td>'+
            '</tr>';
        $('#data-'+category).append(row);
        objectMarker(item.id, item.lat, item.lng);
    }
}

// Show modal for object
function infoModal(id) {
    let title, content;
    if (id.substring(0,2) === "CP") {
        $.ajax({
            url: baseUrl + '/api/culinaryPlace/' + id,
            dataType: 'json',
            success: function (response) {
                let item = response.data;
                let open = item.open.substring(0, item.open.length - 3);
                let close = item.close.substring(0, item.close.length - 3);

                title = '<h3>'+item.name+'</h3>';
                content =
                    '<div class="text-start">'+
                    '<p><span class="fw-bold">Address</span>: '+ item.address +'</p>'+
                    '<p><span class="fw-bold">Open</span>: '+ open +' - '+ close+' WIB</p>'+
                    '<p><span class="fw-bold">Contact Person:</span> '+ item.contact_person+'</p>'+
                    '<p><span class="fw-bold">Capacity</span>: '+ item.capacity+'</p>'+
                    '<p><span class="fw-bold">Employee</span>: '+ item.employee+'</p>'+
                    '</div>'+
                    '<div>' +
                    '<img src="'+ baseUrl +'/media/photos/'+ item.gallery[0] +'" alt="'+ item.name +'" class="w-50">' +
                    '</div>';

                Swal.fire({
                    title: title,
                    html: content,
                    width: '50%',
                    position: 'top'
                });
            }
        });
    } else if (id.substring(0,2) === "WP") {
        $.ajax({
            url: baseUrl + '/api/worshipPlace/' + id,
            dataType: 'json',
            success: function (response) {
                let item = response.data;

                title = '<h3>'+item.name+'</h3>';
                content =
                    '<div class="text-start">'+
                    '<p><span class="fw-bold">Address</span>: '+ item.address +'</p>'+
                    '<p><span class="fw-bold">Park Area :</span> '+ item.park_area_size+' m<sup>2</sup></p>'+
                    '<p><span class="fw-bold">Building Area</span>: '+ item.building_size+' m<sup>2</sup></p>'+
                    '<p><span class="fw-bold">Capacity</span>: '+ item.capacity+'</p>'+
                    '<p><span class="fw-bold">Last Renovation</span>: '+ item.last_renovation+'</p>'+
                    '</div>' +
                    '<div>' +
                    '<img src="'+ baseUrl +'/media/photos/'+ item.gallery[0] +'" alt="'+ item.name +'" class="w-50">' +
                    '</div>';

                Swal.fire({
                    title: title,
                    html: content,
                    width: '50%',
                    position: 'top'
                });
            }
        });
    } else if (id.substring(0,2) === "SP") {
        $.ajax({
            url: baseUrl + '/api/souvenirPlace/' + id,
            dataType: 'json',
            success: function (response) {
                let item = response.data;
                let open = item.open.substring(0, item.open.length - 3);
                let close = item.close.substring(0, item.close.length - 3);

                title = '<h3>'+item.name+'</h3>';
                content =
                    '<div class="text-start">'+
                    '<p><span class="fw-bold">Address</span>: '+ item.address +'</p>'+
                    '<p><span class="fw-bold">Contact Person :</span> '+ item.contact_person+'</p>'+
                    '<p><span class="fw-bold">Employee</span>: '+ item.employee+'</p>'+
                    '<p><span class="fw-bold">Open</span>: '+ open +' - '+ close+' WIB</p>'+
                    '</div>' +
                    '<div>' +
                    '<img src="'+ baseUrl +'/media/photos/'+ item.gallery[0] +'" alt="'+ item.name +'" class="w-50">' +
                    '</div>';

                Swal.fire({
                    title: title,
                    html: content,
                    width: '50%',
                    position: 'top'
                });
            }
        });
    }
}

// Find object by name
function findByName(category) {
    clearRadius();
    clearRoute();
    clearMarker();
    destinationMarker.setMap(null);
    clearUser();
    google.maps.event.clearListeners(map, 'click');
    $('#direction-row').hide();
    $('#check-nearby-col').hide();
    $('#result-nearby-col').hide();
    $('#list-rg-col').show();
    $('#list-ev-col').show();

    let name;
    if (category === 'RG') {
        name = document.getElementById('nameRG').value;
        $.ajax({
            url: baseUrl + '/api/rumahGadang/findByName',
            type: 'POST',
            data: {
                name: name,
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                boundToObject();
            }
        });
    } else if (category === 'EV') {
        name = document.getElementById('nameEV').value;
        $.ajax({
            url: baseUrl + '/api/event/findByName',
            type: 'POST',
            data: {
                name: name,
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                boundToObject();
            }
        });
    }
}

// Get list of Rumah Gadang facilities
function getFacility() {
    let facility;
    $('#facilitySelect').empty()
    $.ajax({
        url: baseUrl + '/api/facility',
        dataType: 'json',
        success: function (response) {
            let data = response.data;
            for (i in data) {
                let item = data[i];
                facility =
                    '<option value="'+ item.id +'">'+ item.facility +'</option>';
                $('#facilitySelect').append(facility);
            }
        }
    });
}

// Find object by Rating
function findByFacility() {
    clearRadius();
    clearRoute();
    clearMarker();
    destinationMarker.setMap(null);
    clearUser();
    google.maps.event.clearListeners(map, 'click');
    $('#direction-row').hide();
    $('#check-nearby-col').hide();
    $('#result-nearby-col').hide();
    $('#list-rg-col').show();
    $('#list-ev-col').show();

    let facility = document.getElementById('facilitySelect').value;
    $.ajax({
        url: baseUrl + '/api/rumahGadang/findByFacility',
        type: 'POST',
        data: {
            facility: facility,
        },
        dataType: 'json',
        success: function (response) {
            displayFoundObject(response);
            boundToObject();
        }
    });
}

// Set star by user input
function setStar(star) {
    switch (star) {
        case 'star-1' :
            $("#star-1").addClass('star-checked');
            $("#star-2,#star-3,#star-4,#star-5").removeClass('star-checked');
            document.getElementById('star-rating').value = '1';
            break;
        case 'star-2' :
            $("#star-1,#star-2").addClass('star-checked');
            $("#star-3,#star-4,#star-5").removeClass('star-checked');
            document.getElementById('star-rating').value = '2';
            break;
        case 'star-3' :
            $("#star-1,#star-2,#star-3").addClass('star-checked');
            $("#star-4,#star-5").removeClass('star-checked');
            document.getElementById('star-rating').value = '3';
            break;
        case 'star-4' :
            $("#star-1,#star-2,#star-3,#star-4").addClass('star-checked');
            $("#star-5").removeClass('star-checked');
            document.getElementById('star-rating').value = '4';
            break;
        case 'star-5' :
            $("#star-1,#star-2,#star-3,#star-4,#star-5").addClass('star-checked');
            document.getElementById('star-rating').value = '5';
            break;
    }
    console.log(document.getElementById('star-rating').value)
}

// Find object by Rating
function findByRating(category) {
    clearRadius();
    clearRoute();
    clearMarker();
    destinationMarker.setMap(null);
    clearUser();
    google.maps.event.clearListeners(map, 'click');
    $('#direction-row').hide();
    $('#check-nearby-col').hide();
    $('#result-nearby-col').hide();
    $('#list-rg-col').show();
    $('#list-ev-col').show();

    let rating = document.getElementById('star-rating').value;
    if (category === 'RG') {
        $.ajax({
            url: baseUrl + '/api/rumahGadang/findByRating',
            type: 'POST',
            data: {
                rating: rating,
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                boundToObject();
            }
        });
    } else if (category === 'EV') {
        $.ajax({
            url: baseUrl + '/api/event/findByRating',
            type: 'POST',
            data: {
                rating: rating,
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                boundToObject();
            }
        });
    }
}

// Find object by Category
function findByCategory(object) {
    clearRadius();
    clearRoute();
    clearMarker();
    destinationMarker.setMap(null);
    clearUser();
    google.maps.event.clearListeners(map, 'click');
    $('#direction-row').hide();
    $('#check-nearby-col').hide();
    $('#result-nearby-col').hide();
    $('#list-rg-col').show();
    $('#list-ev-col').show();

    if (object === 'RG') {
        let category = document.getElementById('categoryRGSelect').value;
        $.ajax({
            url: baseUrl + '/api/rumahGadang/findByCategory',
            type: 'POST',
            data: {
                category: category,
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                boundToObject();
            }
        });
    } else if (object === 'EV') {
        let category = document.getElementById('categoryEVSelect').value;
        $.ajax({
            url: baseUrl + '/api/event/findByCategory',
            type: 'POST',
            data: {
                category: category,
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                boundToObject();
            }
        });
    }
}

// Get list of Event category
function getCategory() {
    let category;
    $('#categoryEVSelect').empty()
    $.ajax({
        url: baseUrl + '/api/event/category',
        dataType: 'json',
        success: function (response) {
            let data = response.data;
            for (i in data) {
                let item = data[i];
                category =
                    '<option value="'+ item.id +'">'+ item.category +'</option>';
                $('#categoryEVSelect').append(category);
            }
        }
    });
}

function findByDate() {
    clearRadius();
    clearRoute();
    clearMarker();
    destinationMarker.setMap(null);
    clearUser();
    google.maps.event.clearListeners(map, 'click');
    $('#direction-row').hide();
    $('#check-nearby-col').hide();
    $('#result-nearby-col').hide();
    $('#list-rg-col').show();
    $('#list-ev-col').show();

    let eventDate = document.getElementById('eventDate').value;
    $.ajax({
        url: baseUrl + '/api/event/findByDate',
        type: 'POST',
        data: {
            date: eventDate,
        },
        dataType: 'json',
        success: function (response) {
            displayFoundObject(response);
            boundToObject();
        }
    });
}


// Create legend
function getLegend() {
    const icons = {
        rg :{
            name: 'Rumah Gadang',
            icon: baseUrl + '/media/icon/marker_rg.png',
        },
        ev :{
            name: 'Event',
            icon: baseUrl + '/media/icon/marker_ev.png',
        },
        cp :{
            name: 'Culinary Place',
            icon: baseUrl + '/media/icon/marker_cp.png',
        },
        wp :{
            name: 'Worship Place',
            icon: baseUrl + '/media/icon/marker_wp.png',
        },
        sp :{
            name: 'Souvenir Place',
            icon: baseUrl + '/media/icon/marker_sp.png',
        },
    }

    const title = '<p class="fw-bold fs-6">Legend</p>';
    $('#legend').append(title);

    for (key in icons) {
        const type = icons[key];
        const name = type.name;
        const icon = type.icon;
        const div = '<div><img src="' + icon + '"> ' + name +'</div>';

        $('#legend').append(div);
    }
    map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(legend);
}

// toggle legend element
function viewLegend() {
    if ($('#legend').is(':hidden')) {
        $('#legend').show();
    } else {
        $('#legend').hide();
    }
}

// list object for new visit history
function getObjectByCategory(){
    const category = document.getElementById('category').value;
    $('#object').empty();
    if (category === 'None') {
        return Swal.fire({
            icon: 'warning',
            title: 'Please Choose a Object Category!'
        });
    }
    if (category === 'RG') {
        $.ajax({
            url: baseUrl + '/api/rumahGadang',
            dataType: 'json',
            success: function (response) {
                let data = response.data;
                for (i in data) {
                    let item = data[i];
                    object =
                        '<option value="'+ item.id +'">'+ item.name +'</option>';
                    $('#object').append(object);
                }
            }
        });
    } else if (category === 'EV') {
        $.ajax({
            url: baseUrl + '/api/event',
            dataType: 'json',
            success: function (response) {
                let data = response.data;
                for (i in data) {
                    let item = data[i];
                    object =
                        '<option value="'+ item.id +'">'+ item.name +'</option>';
                    $('#object').append(object);
                }
            }
        });
    }
}