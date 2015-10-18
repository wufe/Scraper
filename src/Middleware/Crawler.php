<?php

	namespace Scraper\Middleware;
	use Scraper\Middleware\Log;
	use Symfony\Component\DomCrawler\Crawler as DomCrawler;

	class Crawler{

		// Crawl the source into a DOM object
		public static function crawl( $source ){
			$crawler = new DomCrawler( $source );
			Log::log( "Listing node attributes" );
			
			$node_tree = Parser::parse( $crawler );

			// Test on google.it
			//var_dump( $crawler->filterXPath( "descendant-or-self::html/body/center/form/table/tr/td[position()=2]/span[position()=1]/span/input" )->getNode(0) );
		}

	}


?>