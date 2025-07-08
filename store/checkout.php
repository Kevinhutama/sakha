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
        <form class="form-group">
          <div class="row d-flex flex-wrap">
            <div class="col-lg-7">
              <h3 class="pb-4">Billing Details</h3>
              <div class="billing-details">
                <div class="py-3">
                  <label for="fname">First Name*</label>
                  <input type="text" id="fname" name="firstname" class="w-100">
                </div>

                <div class="py-3">
                  <label for="lname">Last Name*</label>
                  <input type="text" id="lname" name="lastname" class="w-100">
                </div>

                <div class="py-3">
                  <label for="address">Street Address*</label>
                  <input type="text" id="adr" name="address" placeholder="House number and street name" class="w-100">
                  <input type="text" id="adr2" name="address2" placeholder="Appartments, suite, etc." class="w-100">
                </div>

                <div class="py-3">
                  <label for="map-search">Search Address on Map</label>
                  <input type="text" id="map-search" placeholder="Search for your address in Indonesia..." class="w-100 mb-3">
                  <div id="map" style="height: 300px; width: 100%; border: 1px solid #ddd; border-radius: 5px; background-color: #f8f9fa;"></div>
                  <small class="text-muted">Click on the map to select your address. You can also drag the marker to fine-tune your location.</small>
                </div>

                <div class="py-3">
                  <label for="city">City *</label>
                  <input type="text" id="city" name="city" class="w-100">
                </div>

                <div class="py-3">
                  <label for="state">Province *</label>
                  <select class="w-100" aria-label="Default select example">
                    <option selected="" hidden="">DKI Jakarta</option>
                    <option value="1">West Java</option>
                    <option value="2">Central Java</option>
                    <option value="3">East Java</option>
                    <option value="4">Bali</option>
                    <option value="5">North Sumatra</option>
                    <option value="6">South Sumatra</option>
                    <option value="7">West Sumatra</option>
                    <option value="8">Yogyakarta</option>
                    <option value="9">South Sulawesi</option>
                    <option value="10">North Sulawesi</option>
                  </select>
                </div>

                <div class="py-3">
                  <label for="zip">Postal Code *</label>
                  <input type="text" id="zip" name="zip" class="w-100" placeholder="e.g. 12345">
                </div>

                <div class="py-3">
                  <label for="email">Phone *</label>
                  <input type="text" id="phone" name="phone" class="w-100">
                </div>
                
                <div class="py-3">
                  <label for="email">Email address *</label>
                  <input type="text" id="email" name="email" class="w-100">
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
            // Initialize map centered on Jakarta, Indonesia
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 11,
                center: {lat: -6.2088, lng: 106.8456}
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
            document.getElementById('adr').value = '';
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
                        const stateSelects = document.querySelectorAll('select[aria-label="Default select example"]');
                        const provinceSelect = stateSelects[1]; // Second dropdown is the province dropdown
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

            // Combine street number and name
            if (streetNumber && streetName) {
                document.getElementById('adr').value = streetNumber + ' ' + streetName;
            } else if (streetName) {
                document.getElementById('adr').value = streetName;
            } else if (place.formatted_address) {
                // Fallback to formatted address
                document.getElementById('adr').value = place.formatted_address;
            }
        }
    </script>
    
    <?php include 'includes/auth-scripts.php'; ?>
  </body>
</html>