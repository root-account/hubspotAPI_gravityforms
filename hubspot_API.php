<?php

/*

To use, copy this code into your functions.php file of your wordpress theme file and make a few edits to target your gravity forms

If you dont want to copy the code, just require or include this file in your functions.php file.

*/


// The number 5 on gform_after_submission_5 is the ID or the form you want to target
add_action( 'gform_after_submission_5', 'post_to_third_party', 10, 2 );
function post_to_third_party( $entry, $form ) {


            //YOUR HUBSPOT API KEY
            $hapikey='';

            /****************************
                GET DATA FROM THE FORM
            *****************************/

            // The number 21 on rgar( $entry, '21' )... is the field number of the form you want to submit

            // Contact details
            $company_name = rgar( $entry, '21' );
            $company_desc = rgar( $entry, '29' );
            $company_site = rgar( $entry, '22' );

            // Contact details
            $contact_fname = rgar( $entry, '67' );
            $contact_sname = rgar( $entry, '68' );
            $contact_phone = rgar( $entry, '71' );
            $contact_email = rgar( $entry, '70' );

            // Deal Details
            $deal_name = $company_name;
            $deal_amount = rgar( $entry, '13' );


            /****************************
                CREATE A NEW CONTACT
            *****************************/
            $contact_arr = array('properties' => array(
                        array(
                            'property' => 'email',
                            'value' => $contact_email
                        ),
                        array(
                            'property' => 'firstname',
                            'value' => $contact_fname
                        ),
                        array(
                            'property' => 'lastname',
                            'value' => $contact_sname
                        ),
                        array(
                            'property' => 'phone',
                            'value' => $contact_phone
                        )
                    )
                );          
            
            
            $contact_json = json_encode($contact_arr);
            $new_contact_endpoint = 'https://api.hubapi.com/contacts/v1/contact/?hapikey='.$hapikey;
                        
            $ch_contact = @curl_init();
            @curl_setopt($ch_contact, CURLOPT_URL, $new_contact_endpoint);
            @curl_setopt($ch_contact, CURLOPT_SSL_VERIFYPEER, false);
            @curl_setopt($ch_contact, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            @curl_setopt($ch_contact, CURLOPT_POST, true);
            @curl_setopt($ch_contact, CURLOPT_POSTFIELDS, $contact_json);
            @curl_setopt($ch_contact, CURLOPT_RETURNTRANSFER, true);
            $contact_response = @curl_exec($ch_contact);
            $contact_status_code = @curl_getinfo($ch_contact, CURLINFO_HTTP_CODE);
            $curl_errors = curl_error($ch_contact);
            @curl_close($ch_contact);

            $cont_response = json_decode($contact_response);

            $contact_ID = $cont_response->vid;

            //echo "Contact ID <h1>".$contact_ID."</h1><br/><br/>";


            /*************************************
                CREATE A COMPANY
            **************************************/

        
            $company_arr = array(
                'properties' => array(
                    array(
                        'name' => 'name',
                        'value' => $company_name
                    ),
                    array(
                        'name' => 'description',
                        'value' => $company_desc
                    )
                )
            );          
        
                        
            $company_json = json_encode($company_arr);
            $new_company_endpoint = 'https://api.hubapi.com/companies/v2/companies?hapikey='.$hapikey;
                        
            $ch_company = @curl_init();
            @curl_setopt($ch_company, CURLOPT_URL, $new_company_endpoint);
            @curl_setopt($ch_company, CURLOPT_SSL_VERIFYPEER, false);
            @curl_setopt($ch_company, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            @curl_setopt($ch_company, CURLOPT_POST, true);
            @curl_setopt($ch_company, CURLOPT_POSTFIELDS, $company_json);
            @curl_setopt($ch_company, CURLOPT_RETURNTRANSFER, true);
            $company_response = @curl_exec($ch_company);
            $company_status_code = @curl_getinfo($ch_company, CURLINFO_HTTP_CODE);
            $curl_errors = curl_error($ch_company);
            @curl_close($ch_company);

            $com_response = json_decode($company_response);

            $company_ID = $com_response->companyId;

            // echo "Company ID <h1>".$company_ID."</h1><br/><br/>";
           
            /*******************
                CREATE A DEAL
            *******************/

            $deal_arr = array(
                'properties' => array(
                    array(
                        'name' => 'dealname',
                        'value' => $company_name
                    ),
                    array(
                        'name' => 'dealstage',
                        'value' => 'appointmentscheduled'
                    ),
                    array(
                        'name' => 'pipeline',
                        'value' => 'default'
                    ),
                    array(
                        'name' => 'amount',
                        'value' => $deal_amount
                    )
                )
            );          
                
                        
            $deal_json = json_encode($deal_arr);
            $new_deal_endpoint = 'https://api.hubapi.com/deals/v1/deal?hapikey='.$hapikey;
                        
            $ch_deal = @curl_init();
            @curl_setopt($ch_deal, CURLOPT_URL, $new_deal_endpoint);
            @curl_setopt($ch_deal, CURLOPT_SSL_VERIFYPEER, false);
            @curl_setopt($ch_deal, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            @curl_setopt($ch_deal, CURLOPT_POST, true);
            @curl_setopt($ch_deal, CURLOPT_POSTFIELDS, $deal_json);
            @curl_setopt($ch_deal, CURLOPT_RETURNTRANSFER, true);
            $deal_response = @curl_exec($ch_deal);
            $deal_status_code = @curl_getinfo($ch_deal, CURLINFO_HTTP_CODE);
            $curl_errors = curl_error($ch_deal);
            @curl_close($ch_deal);

            $de_response = json_decode($deal_response);

            $deal_ID = $de_response->dealId;

            // echo "Deal ID <h1>".$deal_ID."</h1><br/><br/>";
            



       /*************************************
            ASSOCIATE A DEAL TO A CONTACT
        *************************************/
        $new_deal_assoc_endpoint = 'https://api.hubapi.com/deals/v1/deal/'.$deal_ID.'/associations/CONTACT?id='.$contact_ID.'&COMPANY?id='.$company_ID.'&hapikey='.$hapikey;
                    
        $ch_deal_assoc = @curl_init();
        @curl_setopt($ch_deal_assoc, CURLOPT_URL, $new_deal_assoc_endpoint);
        @curl_setopt($ch_deal_assoc, CURLOPT_SSL_VERIFYPEER, false);
        @curl_setopt($ch_deal_assoc, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        @curl_setopt($ch_deal_assoc, CURLOPT_PUT, true);
        @curl_setopt($ch_deal_assoc, CURLOPT_RETURNTRANSFER, true);
        $deal_assoc_response = @curl_exec($ch_deal_assoc);
        $deal_assoc_status_code = @curl_getinfo($ch_deal_assoc, CURLINFO_HTTP_CODE);
        $curl_errors = curl_error($ch_deal_assoc);
        @curl_close($ch_deal_assoc);



        /*************************************
            ASSOCIATE A COMPANY TO A CONTACT
        *************************************/
        $new_company_assoc_endpoint = 'https://api.hubapi.com/companies/v2/companies/'.$company_ID.'/contacts/'.$contact_ID.'?hapikey='.$hapikey;
                          
        $ch_company_assoc = @curl_init();
        @curl_setopt($ch_company_assoc, CURLOPT_URL, $new_company_assoc_endpoint);
        @curl_setopt($ch_company_assoc, CURLOPT_SSL_VERIFYPEER, false);
        @curl_setopt($ch_company_assoc, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        @curl_setopt($ch_company_assoc, CURLOPT_PUT, true);
        @curl_setopt($ch_company_assoc, CURLOPT_RETURNTRANSFER, true);
        $company_assoc_response = @curl_exec($ch_company_assoc);
        $company_assoc_status_code = @curl_getinfo($ch_company_assoc, CURLINFO_HTTP_CODE);
        $curl_errors = curl_error($ch_company_assoc);
        @curl_close($ch_company_assoc);







        /*******************************************
         SUBMIT TO A CUSTOM FORM CREATED IN HUBSPOT
        ********************************************/

        //Process a new form submission in HubSpot in order to create a new Contact.

        $hubspotutk      = $_COOKIE['hubspotutk']; //grab the cookie from the visitors browser.
        $ip_addr         = $_SERVER['REMOTE_ADDR']; //IP address too.
        $hs_context      = array(
            'hutk' => $hubspotutk,
            'ipAddress' => $ip_addr,
            'pageUrl' => 'https://vumelafund.com/get-funded/',
            'pageName' => 'Get Funded'
        );
        $hs_context_json = json_encode($hs_context);

        //Need to populate these variable with values from the form.
        $str_post = "firstname=" . urlencode($contact_fname) 
                    . "&lastname=" . urlencode($contact_sname)  
                    . "&phone=" . urlencode($contact_phone)  
                    . "&email=" . urlencode($contact_email)  
                    . "&hs_context=" . urlencode($hs_context_json); //Leave this one be

        //https://share.hsforms.com/
        //replace the values in this URL with your portal ID and your form GUID

        $endpoint = 'https://forms.hubspot.com/uploads/form/v2/2937431/167f79f7-e6b1-49c1-80a1-6bbcf0aa9d60';

        $ch = @curl_init();
        @curl_setopt($ch, CURLOPT_POST, true);
        @curl_setopt($ch, CURLOPT_POSTFIELDS, $str_post);
        @curl_setopt($ch, CURLOPT_URL, $endpoint);
        @curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded'
        ));
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response    = @curl_exec($ch); //Log the response from HubSpot as needed.
        $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE); //Log the response status code
        @curl_close($ch);
        echo $status_code . " " . $response;





}