window.onload = function() {
				
				var address1 = "{{ address1|escape('js') }}";
				var address2 ="{{ address2|escape('js') }}";
				var zipcode = "{{ zipcode|escape('js') }}";
				var city = "{{ city|escape('js') }}";
				
				var myMarker = null;
			 
				var myLatlng = new google.maps.LatLng(-58.397, 15.644);
			 
				var myOptions = {
					zoom: 15,
					center: myLatlng,
					mapTypeId: google.maps.MapTypeId.ROADMAP
				};
			 
				var myMap = new google.maps.Map(
					document.getElementById('map'),
					myOptions
				);
			 
				var GeocoderOptions = {
				    'address' : address1,
				    'region' : 'FR'
				}
			
				function GeocodingResult( results , status )
				{
			
				  if( status == google.maps.GeocoderStatus.OK ) {
			 
				    if(myMarker != null) {
				      myMarker.setMap(null);
				    }
			 
				    myMarker = new google.maps.Marker({
				      position: results[0].geometry.location,
				      map: myMap,
				      title: "{{ branch.name }}"
				    });
			 
				    myMap.setCenter(results[0].geometry.location);
				  } 
				} 
				var myGeocoder = new google.maps.Geocoder();
				myGeocoder.geocode( GeocoderOptions, GeocodingResult );
			}
