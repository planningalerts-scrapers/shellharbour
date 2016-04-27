<?php
# Shellharbour City Council scraper
require 'scraperwiki.php';
require 'simple_html_dom.php';
date_default_timezone_set('Australia/Sydney');


###
### Main code start here
###
$url_base    = "https://eservices.shellharbour.nsw.gov.au/T1PRProd/WebApps/eProperty/P1/PublicNotices/";
$da_page     = $url_base . "AllPublicNotices.aspx?r=SCC.WEBGUEST&f=SCC.ESB.PUBNOTAL.ENQ";
$comment_url = "http://www.shellharbour.nsw.gov.au/Contact-Us.aspx";

# Get the data that I want within the page
$dom = file_get_html($da_page);

foreach($dom->find("table[class=grid]") as $record ) {
    $application = [];
   
    $application['council_reference'] = trim($record->find("td", 1)->plaintext);
    $application['address']           = trim($record->find("td", 5)->plaintext);
    $application['description']       = trim($record->find("td",3)->plaintext);
    $application['info_url']          = $url_base . html_entity_decode($record->find("a", 0)->href);
    $application['comment_url']       = $comment_url;
    $application['date_scraped']      = date('Y-m-d');
    $date = explode("/", $record->find("td", 7)->plaintext);
    $application['on_notice_to']      = $date[2]."-".$date[1]."-".$date[0];

    # Check if record exist, if not, INSERT, else do nothing
    $existingRecords = scraperwiki::select("* from data where `council_reference`='" . $application['council_reference'] . "'");
    if ( count($existingRecords) == 0 ) {
        print ("Saving record " . $application['council_reference'] . "\n");
        # print_r ($application);
        scraperwiki::save(array('council_reference'), $application);
    } else {
        print ("Skipping already saved record or ignore corrupted data - " . $application['council_reference'] . "\n");
    }

}

?>
