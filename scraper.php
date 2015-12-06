<?php
# Shellharbour City Council scraper
require 'scraperwiki.php';
require 'simple_html_dom.php';
date_default_timezone_set('Australia/Sydney');


###
### Main code start here
###
$url_base = "http://www.oasis.shellharbour.nsw.gov.au";
$da_page  = $url_base . "/index.pl?page=3442";

# Get the data that I want within the page
$dom = file_get_html($da_page);

foreach($dom->find("tr[bgcolor=#DEE1E7]") as $record ) {

    $application = array('council_reference' => '', 'address' => '', 'description' => '', 'info_url' => '', 
                         'comment_url' => '', 'date_scraped' => '', 'date_received' => '');

    foreach($record->find('tr') as $gem) {
        if ((!is_null($gem->find("td", 0))) && (!is_null($gem->find("td", 1)))) {
            $key = trim($gem->find("td", 0)->plaintext);
            $value = preg_replace('/\s+/', ' ', trim($gem->find("td", 1)->plaintext));
        }

        switch ($key) {
            case 'DA Number:' :
                $value = str_replace("Lodge a Submission", "", $value);
                $application['council_reference'] = $value;
                $application['comment_url']       = $url_base . $gem->find("a", 0)->href;
                break;            
            case 'Address:' :
                if (!empty($value)) {
                    $application['address'] = $value . ", Australia";
                } else {
                    $application['address'] = " ";
                }
                break;
            case 'Description:' :
                $application['description'] = $value;
                break;
            case 'Notification Expires:' :
                $date_received = substr($value, 4);
                $application['on_notice_to']  = date('Y-m-d', strtotime($date_received));
                $application['date_received'] = date('Y-m-d', strtotime($date_received) - (14*24*60*60));   # Submit date = Notification - 14 days (Guessing??)               
                break;                
        }
    }
    $application['info_url'] = $da_page;
    $application['date_scraped'] = date('Y-m-d');

    # Check if record exist, if not, INSERT, else do nothing
    $existingRecords = scraperwiki::select("* from data where `council_reference`='" . $application['council_reference'] . "'");
    if ((count($existingRecords) == 0) && ($application['council_reference'] !== 'Not on file')) {
        print ("Saving record " . $application['council_reference'] . "\n");
        # print_r ($application);
        scraperwiki::save(array('council_reference'), $application);
    } else {
        print ("Skipping already saved record or ignore corrupted data - " . $application['council_reference'] . "\n");
    }

}

?>
