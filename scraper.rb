require "technology_one_scraper"

TechnologyOneScraper.scrape_and_save_period(
  url: "https://eservices.shellharbour.nsw.gov.au/T1PRProd/WebApps/eProperty",
  period: "L28",
  webguest: "SCC.WEBGUEST",
  # Looks like the site has an incomplete certificate chain
  disable_ssl_certificate_check: true
)
