<?php
// GET CITY & COUNTRY BY LATITUDE & LONGITUDE

// Initialize
error_reporting(0); // enable error & warring hide
$fl = 1; 
$arr = array();


// CHECK PARAM
if( !isset( $_GET[ 'lat' ] ) ) {
    $arr[ 'message' ] = "No Latitude found !" ;
    $fl = 0;
}
if( empty( $_GET[ 'lat' ] ) ) {
    $arr[ 'message' ] = "No Latitude found !" ;
    $fl = 0;
}
if( !is_numeric( $_GET[ 'lat' ] ) ) {
    $arr[ 'message' ] = "Invalid Latitude !" ;
    $fl = 0;
}
if( !isset( $_GET[ 'lng' ] ) ) {
    $arr[ 'message' ] = "No Longitude found !" ;
    $fl = 0;
}
if( empty( $_GET[ 'lng' ] ) ) {
    $arr[ 'message' ] = "No Longitude found !" ;
    $fl = 0;
}
if( empty( $_GET[ 'lng' ] ) ) {
    $arr[ 'message' ] = "No Longitude !" ;
    $fl = 0;
}


// IF, all param are in correct way 
if( $fl == 1 ) { 

    // Initialize
    $total_line = "";
    $line_fl = 0;
    $line_fl2 = 0;
    $city = "";
    $country = "";
    $line_fl3 = 0;
    
    
    // CATCH REQUEST 
    $lat = strip_tags( $_GET[ 'lat' ] );
    $lng = strip_tags( $_GET[ 'lng' ] );
    
    // REQUEST URL
    $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$lng."&sensor=false";
    

    // CURL for GET ADDRESS DETAILS 
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    $output=curl_exec($ch);
    if($output == false) {
        $fl = 0;
    }
    curl_close($ch); // cURL close
    
    
    // Default values for finding city 
    $segmants = array();
    $add_start_fl = 0;
    $total_seg = "";
    $address_details = strstr($output, "address_components");
    $seg_fl = 0 ;  
    $seg_index = -1 ; // Initialize an default array index
    
    // Separate into sagment for check locality/city 
    for( $i = 0 ; $i < strlen( $address_details ) ; $i++ ) {
    
        // Find Start Point 
        if( $address_details[ $i ] == "[" ) {
            $add_start_fl = 1;
            continue;
        }
        
        // Find End Point 
        if( $address_details[ $i ] == "]" ) {
            if( $address_details[ $i+1 ] == "," ) {
                break;
            }
            continue;
        }
        
        // Add Data into Segments
        if( $add_start_fl == 1 ) {
            $total_seg .= $address_details[ $i ];
            if( $address_details[ $i ] == "{" ) {
                $seg_fl = 1 ;
                // Increase index by 1
                $seg_index++;
                // Set array as NULL
                $segmants[ $seg_index ] = "" ; 
                continue;
            }
            
            // End Segment data flag
            if( $address_details[ $i ] == "}" ) {
                $seg_fl = 0;
                continue;
            }
            
            // If Segment Flag is 1 
            if( $seg_fl == 1 ) {
                // Add data into array
                $segmants[ $seg_index ] .= $address_details[ $i ]; 
            }
        }
    }
    
    // Find locality line from segments
    $total_segment = sizeof( $segmants );
    $country_slog = $segmants[ $total_segment-1 ];
    $city_slog = "" ;
    for( $i = $total_segment-1 ; $i >= 0 ; $i-- ) {
        if( strstr( $segmants[ $i ] , "locality" ) == true ) {
            $city_slog .= $segmants[ $i ];
            break;
        }
    }
    $city_slog = strstr( $city_slog , "long_name" ) ;
    // Find locality line from segments end
    
    // Get city name from locality line
    $city_check_fl = 0;
    $city = "";
    for( $i = 0 ; $i < strlen( $city_slog ); $i++ ) {
        if( $city_slog[ $i ] == ":" ) {
            $city_check_fl=1;
            continue;
        }
        if( $city_slog[ $i ] == "," ) {
            break;
        }
        if( $city_check_fl == 1 ) {
            if( $city_slog[ $i ] == '"' ) {
                continue;
            }
            $city .= $city_slog[ $i ];
        }
    }
    // Get city name from locality line end
    
    
    // FIND LINE where CITY and COUNTRY available 
    $check_param = strstr( $output , "formatted_address" );
    
    for( $i = 0 ; $i < strlen( $check_param ) ; $i++ ) {
        if( $check_param[ $i ] == ":" ) {
            $line_fl++;
            continue;
        }
        if( $line_fl == 1 ) {
            if( $check_param[ $i ] == '"' ) {
                $line_fl2++;
                continue;
            }
            if( $line_fl2 == 1 ) {
                $total_line .= $check_param[ $i ];
            }
            if( $line_fl2 > 1 ) {
                break;
            }
        }
        if( $line_fl > 1 ) {
            break;
        }
    }
    
    // GET COUNTRY FROM PREVIOUSLY FORMATTED LINE 
    for( $i = strlen( $total_line )-1 ; $i > 0 ; $i-- ) {
        if( $total_line[ $i ] == "," ) {
            break;
        }
        if( !is_numeric( $total_line[ $i ] ) ) {
            $country .= $total_line[ $i ];
        }
    }
    
    // CHECK CITY & COUNTRY ARE CORRECT
    if( $country != "" && $city != "" ) {
        $arr[ "city" ] = str_replace(" ", "", $city );
        $arr[ "country" ] = strrev( str_replace( " " , "" , $country ) );
    }
    else {
        $arr[ 'message' ] = "No City & Country found !" ;
        $fl = 0;
    }
}
// SET RESPONSE STATUS and MESSAGE
$arr[ "status" ] = $fl ;
$res = json_encode( $arr );
echo $res;
?>
