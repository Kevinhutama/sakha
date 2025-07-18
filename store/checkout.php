<?php
// Initialize session
require_once 'includes/session-config.php';
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Vaso Ecommerce Template</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link rel="stylesheet" type="text/css" href="css/vendor.css">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Italiana&family=Mulish:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;0,1000;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900;1,1000&display=swap" rel="stylesheet">
    <!-- script
    ================================================== -->
    <script src="js/modernizr.js"></script>
  </head>
  <body>
    <?php include 'includes/svg-icons.php'; ?>
    
    <div id="preloader">
      <div id="loader"></div>
    </div>
    <?php include 'includes/navigation.php'; ?>
    <section class="hero-section jarallax d-flex align-items-center justify-content-center padding-medium pb-5" style="background: url(images/hero-img.jpg) no-repeat;">
      <div class="hero-content">
        <div class="container">
          <div class="row">
            <div class="text-center padding-large no-padding-bottom">
              <h1>Checkout</h1>
              <div class="breadcrumbs">
                <span class="item">
                  <a href="index.php">Home ></a>
                </span>
                <span class="item">Checkout</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="shopify-cart checkout-wrap padding-large">
      <div class="container">
        <form class="form-group" id="checkout-form">
          <div class="row d-flex flex-wrap">
            <div class="col-lg-7">
              <h3 class="pb-4">Billing Details</h3>
              <div class="billing-details">
                <div class="py-3">
                  <label for="fname">Name*</label>
                  <input type="text" id="fname" name="firstname" class="w-100">
                </div>

                <div class="py-3">
                  <label for="phone">Phone Number*</label>
                  <input type="tel" id="phone" name="phone" class="w-100" placeholder="e.g. 08123456789">
                </div>

                <div class="py-3">
                  <label for="province">Province *</label>
                  <select id="province" name="province" class="w-100" aria-label="Select Province">
                    <option selected="" hidden="">Select Province</option>
                    <option value="Aceh">Aceh</option>
                    <option value="Bali">Bali</option>
                    <option value="Banten">Banten</option>
                    <option value="Bengkulu">Bengkulu</option>
                    <option value="DI Yogyakarta">DI Yogyakarta</option>
                    <option value="DKI Jakarta">DKI Jakarta</option>
                    <option value="Gorontalo">Gorontalo</option>
                    <option value="Jambi">Jambi</option>
                    <option value="Jawa Barat">Jawa Barat</option>
                    <option value="Jawa Tengah">Jawa Tengah</option>
                    <option value="Jawa Timur">Jawa Timur</option>
                    <option value="Kalimantan Barat">Kalimantan Barat</option>
                    <option value="Kalimantan Selatan">Kalimantan Selatan</option>
                    <option value="Kalimantan Tengah">Kalimantan Tengah</option>
                    <option value="Kalimantan Timur">Kalimantan Timur</option>
                    <option value="Kalimantan Utara">Kalimantan Utara</option>
                    <option value="Kepulauan Bangka Belitung">Kepulauan Bangka Belitung</option>
                    <option value="Kepulauan Riau">Kepulauan Riau</option>
                    <option value="Lampung">Lampung</option>
                    <option value="Maluku">Maluku</option>
                    <option value="Maluku Utara">Maluku Utara</option>
                    <option value="Nusa Tenggara Barat">Nusa Tenggara Barat</option>
                    <option value="Nusa Tenggara Timur">Nusa Tenggara Timur</option>
                    <option value="Papua">Papua</option>
                    <option value="Papua Barat">Papua Barat</option>
                    <option value="Papua Barat Daya">Papua Barat Daya</option>
                    <option value="Papua Pegunungan">Papua Pegunungan</option>
                    <option value="Papua Selatan">Papua Selatan</option>
                    <option value="Papua Tengah">Papua Tengah</option>
                    <option value="Riau">Riau</option>
                    <option value="Sulawesi Barat">Sulawesi Barat</option>
                    <option value="Sulawesi Selatan">Sulawesi Selatan</option>
                    <option value="Sulawesi Tengah">Sulawesi Tengah</option>
                    <option value="Sulawesi Tenggara">Sulawesi Tenggara</option>
                    <option value="Sulawesi Utara">Sulawesi Utara</option>
                    <option value="Sumatera Barat">Sumatera Barat</option>
                    <option value="Sumatera Selatan">Sumatera Selatan</option>
                    <option value="Sumatera Utara">Sumatera Utara</option>
                  </select>
                </div>

                <div class="py-3">
                  <label for="map-search">Street Address</label>
                  <input type="text" id="map-search" placeholder="Your address ..." class="w-100 mb-3">
                  
                  <input type="text" id="adr2" name="address2" placeholder="Other details (e.g. yellow store, apartment, suite, landmarks, etc.)" class="w-100" style="margin-bottom: 10px;">
                  <div id="map" style="height: 300px; width: 100%; border: 1px solid #ddd; border-radius: 5px; background-color: #f8f9fa;"></div>
                  <small class="text-muted">Click on the map to select your address. You can also drag the marker to fine-tune your location.</small>
                </div>

                <div class="py-3">
                  <label for="zip">Postal Code *</label>
                  <input type="text" id="zip" name="zip" class="w-100" placeholder="e.g. 12345">
                </div>
            
              </div>
            </div>
            <div class="col-lg-5">
              <h3 class="pb-4">Additional Information</h3>
              <div class="billing-details">
                <label for="fname">Order notes (optional)</label>
                <textarea class="w-100" placeholder="Notes about your order. Like special notes for delivery."></textarea>
              </div>
              <div class="your-order mt-5">
                <h3 class="pb-4">Cart Totals</h3>
                <div class="total-price">
                  <table cellspacing="0" class="table">
                    <tbody>
                      <tr class="subtotal border-top border-bottom border-dark pt-2 pb-2 text-uppercase">
                        <th>Subtotal</th>
                        <td data-title="Subtotal">
                          <span class="price-amount amount text-primary ps-5">
                            <bdi>
                              <span class="price-currency-symbol">$</span>2,370.00 </bdi>
                          </span>
                        </td>
                      </tr>
                      <tr class="order-total border-bottom border-dark pt-2 pb-2 text-uppercase">
                        <th>Total</th>
                        <td data-title="Total">
                          <span class="price-amount amount text-primary ps-5">
                            <bdi>
                              <span class="price-currency-symbol">$</span>2,370.00 </bdi>
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div class="list-group mt-5 mb-3">
                    <label class="list-group-item p-0 bg-transparent d-flex gap-2 border-0">
                      <input class="form-check-input p-0 flex-shrink-0" type="radio" name="listGroupRadios" id="listGroupRadios1" value="" checked>
                      <span>
                        <div class="fw-300 text-uppercase d-flex align-items-center gap-2">
                          <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/QRIS_logo.svg/2560px-QRIS_logo.svg.png" alt="QRIS" style="height: 24px; width: auto;">
                        </div>
                        <!-- <p class="d-block">Pay using QRIS (Quick Response Code Indonesian Standard). Simply scan the QR code with your mobile banking app or e-wallet to complete the payment instantly.</p> -->
                      </span>
                    </label>

                  </div>
                  <button type="submit" name="submit" class="btn btn-dark w-100">Place an order</button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </section>
    <section id="newsletter" class="bg-light padding-medium" style="background-image: url(images/hero-img.jpg);">
      <div class="container">
        <div class="newsletter">
          <div class="row">
            <div class="col-lg-6 col-md-12 title mb-4">
              <h2>Subscribe to Our Newsletter</h2>
              <p>Get latest news, updates and deals directly mailed to your inbox</p> 				
            </div>
            <form class="col-lg-6 col-md-12 d-flex align-items-center">
              <div class="d-flex w-75 border-bottom border-dark py-2">
                <input id="newsletter1" type="text" class="form-control border-0 p-0" placeholder="Your email address here">
                <button class="btn border-0 p-0" type="button">Subscribe</button>
              </div>
            </form>
          </div> 			
        </div>
      </div>
    </section>
    <footer id="footer" class="overflow-hidden padding-xlarge pb-0">
      <div class="container">
        <div class="row">
          <div class="footer-top-area pb-5">
            <div class="row d-flex flex-wrap justify-content-between">
              <div class="col-lg-3 col-sm-6 pb-3" data-aos="fade" data-aos-easing="ease-in" data-aos-duration="1000" data-aos-once="true">
                <div class="footer-menu">
                  <img src="images/main-logo.png" alt="logo" class="mb-2">
                  <p>Nunc tristique facilisis consectetur vivamus ut porta porta aliquam vitae vehicula leo nullam urna lectus.</p>
                </div>
              </div>
              <div class="col-lg-2 col-sm-6 pb-3" data-aos="fade" data-aos-easing="ease-in" data-aos-duration="1200" data-aos-once="true">
                <div class="footer-menu">
                  <h4 class="widget-title pb-2">Quick Links</h4>
                  <ul class="menu-list list-unstyled">
                    <li class="menu-item pb-2">
                      <a href="about.html">About</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="shop.html">Shop</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="contact.html">Contact</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Account</a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="col-lg-3 col-sm-6 pb-3" data-aos="fade" data-aos-easing="ease-in" data-aos-duration="1400" data-aos-once="true">
                <div class="footer-menu contact-item">
                  <h4 class="widget-title pb-2">Contact info</h4>
                  <ul class="menu-list list-unstyled">
                    <li class="menu-item pb-2">
                      <a href="#">Tea Berry, Marinette, USA</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="#">+55 111 222 333 44</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="mailto:">yourinfo@gmail.com</a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="col-lg-3 col-sm-6 pb-3" data-aos="fade" data-aos-easing="ease-in" data-aos-duration="1600" data-aos-once="true">
                <div class="footer-menu">
                  <h4 class="widget-title pb-2">Social info</h4>
                  <p>You can follow us on our social platforms to get updates.</p>
                  <div class="social-links">
                    <ul class="d-flex list-unstyled">
                      <li>
                        <a href="#">
                          <svg class="facebook">
                            <use xlink:href="#facebook">
                          </svg>
                        </a>
                      </li>
                      <li>
                        <a href="#">
                          <svg class="instagram">
                            <use xlink:href="#instagram">
                          </svg>
                        </a>
                      </li>
                      <li>
                        <a href="#">
                          <svg class="twitter">
                            <use xlink:href="#twitter">
                          </svg>
                        </a>
                      </li>
                      <li>
                        <a href="#">
                          <svg class="linkedin">
                            <use xlink:href="#linkedin">
                          </svg>
                        </a>
                      </li>
                      <li>
                        <a href="#">
                          <svg class="youtube">
                            <use xlink:href="#youtube">
                          </svg>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <hr>
      </div>
    </footer>
    <div id="footer-bottom">
      <div class="container">
        <div class="row d-flex flex-wrap justify-content-between">
          <div class="col-12">
            <div class="copyright">
              <p>© Copyright 2023 Vaso. Design by <a href="https://templatesjungle.com/" target="_blank"><b>TemplatesJungle</b></a></p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <?php include 'includes/login-modal.php'; ?>

    <script src="js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="js/plugins.js"></script>
    <script type="text/javascript" src="js/script.js"></script>
    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAef_gZXRvK3y4TP666us0NglEXRqcXKmM&libraries=places&callback=initMap" async defer></script>
    <script>
        let map;
        let marker;
        let geocoder;
        let autocomplete;

        function initMap() {
            // Initialize map centered on Bandung, Indonesia
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: {lat: -6.9175, lng: 107.6191}
            });

            // Initialize geocoder
            geocoder = new google.maps.Geocoder();

            // Initialize autocomplete on search input
            autocomplete = new google.maps.places.Autocomplete(
                document.getElementById('map-search'),
                {
                    types: ['address'],
                    componentRestrictions: {country: 'id'},
                    bounds: {
                        north: 6.0,
                        south: -11.0,
                        east: 141.0,
                        west: 95.0
                    }
                }
            );

            // Set up autocomplete listener
            autocomplete.addListener('place_changed', onPlaceChanged);

            // Set up map click listener
            map.addListener('click', function(event) {
                placeMarkerAndPanTo(event.latLng);
            });

            // Try to get user's current location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    map.setCenter(userLocation);
                }, function() {
                    // Handle location error
                    console.log('Error: The Geolocation service failed.');
                });
            }
        }

        function onPlaceChanged() {
            const place = autocomplete.getPlace();
            
            if (!place.geometry) {
                console.log("No details available for input: '" + place.name + "'");
                return;
            }

            // If the place has a geometry, center the map on it
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);
            }

            // Place marker
            if (marker) {
                marker.setMap(null);
            }
            marker = new google.maps.Marker({
                position: place.geometry.location,
                map: map,
                draggable: true
            });

            // Make marker draggable and update address on drag
            marker.addListener('dragend', function() {
                updateAddressFromLatLng(marker.getPosition());
            });

            // Update form fields
            updateFormFields(place);
        }

        function placeMarkerAndPanTo(latLng) {
            // Remove existing marker
            if (marker) {
                marker.setMap(null);
            }

            // Add new marker
            marker = new google.maps.Marker({
                position: latLng,
                map: map,
                draggable: true
            });

            // Make marker draggable
            marker.addListener('dragend', function() {
                updateAddressFromLatLng(marker.getPosition());
            });

            // Update address based on clicked location
            updateAddressFromLatLng(latLng);
        }

        function updateAddressFromLatLng(latLng) {
            geocoder.geocode({location: latLng}, function(results, status) {
                if (status === 'OK') {
                    if (results[0]) {
                        const place = results[0];
                        updateFormFields(place);
                        document.getElementById('map-search').value = place.formatted_address;
                    } else {
                        console.log('No results found');
                    }
                } else {
                    console.log('Geocoder failed due to: ' + status);
                }
            });
        }

        function updateFormFields(place) {
            // Clear existing values
            document.getElementById('city').value = '';
            document.getElementById('zip').value = '';

            let streetNumber = '';
            let streetName = '';

            // Parse address components
            if (place.address_components) {
                place.address_components.forEach(function(component) {
                    const types = component.types;
                    
                    if (types.includes('street_number')) {
                        streetNumber = component.long_name;
                    }
                    
                    if (types.includes('route')) {
                        streetName = component.long_name;
                    }
                    
                    if (types.includes('locality')) {
                        document.getElementById('city').value = component.long_name;
                    }
                    
                    if (types.includes('postal_code')) {
                        document.getElementById('zip').value = component.long_name;
                    }
                    
                    if (types.includes('administrative_area_level_1')) {
                        // Update province dropdown if needed
                        const provinceSelect = document.getElementById('province');
                        const provinceName = component.long_name;
                        
                        if (provinceSelect) {
                            // Try to find matching option in dropdown
                            for (let option of provinceSelect.options) {
                                if (option.text.toLowerCase().includes(provinceName.toLowerCase()) || 
                                    option.text.toLowerCase().includes(component.short_name.toLowerCase())) {
                                    option.selected = true;
                                    break;
                                }
                            }
                        }
                    }
                });
            }

            // Update map-search field with formatted address
            if (place.formatted_address) {
                document.getElementById('map-search').value = place.formatted_address;
            }
        }

        // Province coordinates for map centering
        const provinceCoordinates = {
            'Aceh': {lat: 4.695135, lng: 96.7493993},
            'Bali': {lat: -8.4095178, lng: 115.188916},
            'Banten': {lat: -6.4058172, lng: 106.0640179},
            'Bengkulu': {lat: -3.7928451, lng: 102.2607641},
            'DI Yogyakarta': {lat: -7.8753849, lng: 110.4262088},
            'DKI Jakarta': {lat: -6.208763, lng: 106.845599},
            'Gorontalo': {lat: 0.6999372, lng: 122.4467238},
            'Jambi': {lat: -1.4851831, lng: 102.4380581},
            'Jawa Barat': {lat: -6.9034443, lng: 107.6181927},
            'Jawa Tengah': {lat: -7.150975, lng: 110.1402594},
            'Jawa Timur': {lat: -7.5360639, lng: 112.2384017},
            'Kalimantan Barat': {lat: -0.2787808, lng: 111.4752851},
            'Kalimantan Selatan': {lat: -3.0926415, lng: 115.2837585},
            'Kalimantan Tengah': {lat: -1.6814878, lng: 113.3823545},
            'Kalimantan Timur': {lat: 1.6406296, lng: 116.419389},
            'Kalimantan Utara': {lat: 3.0730929, lng: 116.0413889},
            'Kepulauan Bangka Belitung': {lat: -2.7410513, lng: 106.4405872},
            'Kepulauan Riau': {lat: 3.9456514, lng: 108.1428669},
            'Lampung': {lat: -4.5585849, lng: 105.4068079},
            'Maluku': {lat: -3.2384616, lng: 130.1452734},
            'Maluku Utara': {lat: 1.5709993, lng: 127.8087693},
            'Nusa Tenggara Barat': {lat: -8.6529334, lng: 117.3616476},
            'Nusa Tenggara Timur': {lat: -8.6573819, lng: 121.0793705},
            'Papua': {lat: -4.269928, lng: 138.0803529},
            'Papua Barat': {lat: -1.3361154, lng: 133.1747162},
            'Papua Barat Daya': {lat: -7.6145924, lng: 133.6926084},
            'Papua Pegunungan': {lat: -4.0648911, lng: 138.3207261},
            'Papua Selatan': {lat: -6.2288274, lng: 139.9419031},
            'Papua Tengah': {lat: -3.3890292, lng: 136.3563742},
            'Riau': {lat: 0.2933469, lng: 101.7068294},
            'Sulawesi Barat': {lat: -2.8441371, lng: 119.2320784},
            'Sulawesi Selatan': {lat: -3.6687994, lng: 119.9740534},
            'Sulawesi Tengah': {lat: -1.4300254, lng: 121.4456179},
            'Sulawesi Tenggara': {lat: -4.14491, lng: 122.174605},
            'Sulawesi Utara': {lat: 0.6246932, lng: 123.9750018},
            'Sumatera Barat': {lat: -0.7399397, lng: 100.8000051},
            'Sumatera Selatan': {lat: -3.3194374, lng: 103.914399},
            'Sumatera Utara': {lat: 2.1153547, lng: 99.5450974}
        };

        // Handle province selection
        document.getElementById('province').addEventListener('change', function() {
            const selectedProvince = this.value;
            if (selectedProvince && provinceCoordinates[selectedProvince]) {
                const coordinates = provinceCoordinates[selectedProvince];
                
                // Center map on selected province
                map.setCenter(coordinates);
                map.setZoom(8); // Appropriate zoom level for province view
                
                // Clear existing address search
                document.getElementById('map-search').value = '';
                
                // Remove existing marker
                if (marker) {
                    marker.setMap(null);
                }
                
                // Update autocomplete bounds to focus on selected province
                const bounds = new google.maps.LatLngBounds();
                const center = new google.maps.LatLng(coordinates.lat, coordinates.lng);
                bounds.extend(center);
                
                // Expand bounds for better autocomplete results
                const offset = 0.5; // degrees
                bounds.extend(new google.maps.LatLng(coordinates.lat + offset, coordinates.lng + offset));
                bounds.extend(new google.maps.LatLng(coordinates.lat - offset, coordinates.lng - offset));
                
                if (autocomplete) {
                    autocomplete.setBounds(bounds);
                }
            }
        });

        // Prevent form submission on Enter key press
        document.getElementById('checkout-form').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                return false;
            }
        });

        // Also prevent Enter key on individual input fields
        document.querySelectorAll('#checkout-form input, #checkout-form select, #checkout-form textarea').forEach(function(element) {
            element.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
    
    <?php include 'includes/auth-scripts.php'; ?>
  </body>
</html>