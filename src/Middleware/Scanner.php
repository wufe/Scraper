<?php

	namespace Scraper\Middleware;

	use Scraper\Middleware\Log;
	use Scraper\Middleware\Crawler;
	use Scraper\Middleware\Tree;

	class Scanner{

		public static $been_prioritized = [];

		// This function will look for pattern
		public static function scan( $node ){
			$priority = Scanner::scan_for_pattern( $node );

			// Readable test
			$prio = [];
			foreach( $priority as $node ){
				unset( $node[ 'children' ] );
				unset( $node[ 'node' ] );
				$prio[] = $node;
			}
			$priority = $prio;
			// End readable test

			// Test
			Log::log( "Printing results." );
			file_put_contents( "priority", print_r( $priority, true ) );
		}

		// This function will find the leaves of the tree
		public static function scan_for_leaves( $node, $root = false, $leaves = [] ){
			if( !$root )$root = $node;
			if( @!$node[ 'tag' ] && Scanner::has_only_nontagged( Tree::get_parent_from_path( $root, $node[ 'path' ] ) ) ){
				$leaf = Tree::get_parent_from_path( $root, $node[ 'path' ] );
				$leaf[ 'leaf_identified_by' ] = $node[ 'path' ];
				$leaves[] = $leaf;
				return $leaves;
			}else if( @!$node[ 'tag' ] ){
				return $leaves;
			}else{
				if( @!!$node[ 'children' ] ){
					foreach( $node[ 'children' ] as $child ){
						$leaves = Scanner::scan_for_leaves( $child, $root, $leaves );
					}
					return $leaves;
				}else{
					$leaf = Tree::get_node_from_path( $root, $node[ 'path' ] );
					$leaf[ 'leaf_identified_by' ] = $node[ 'path' ];
					$leaves[] = $leaf;
					return $leaves;
				}
			}
		}

		// Checks that a node has only children that do not have the tag attribute
		public static function has_only_nontagged( $node ){
			$ret = true;
			if( @!$node[ 'children' ] ){
				return true;
			}else{
				foreach( $node[ 'children' ] as $child ){
					if( @!!$child[ 'tag' ] ){
						$ret = false;
					}
				}
				return $ret;
			}
		}

		// This function will scan for pattern
		public static function scan_for_pattern( $root, $node = "", $priority = [] ){
			if( @!$node )$node = $root;
			$children = Tree::get_tagged_children( $node );
			if( @!!$children ){
				if( Scanner::are_eligible( $children ) && !Scanner::has_been_prioritized( $node[ 'path' ] ) ){
					$node[ 'count' ] = count( $children );
					$id = Scanner::get_id( $node );
					if( $id !== false ){
						$node[ 'identifier' ] = "#".$id;
					}
					
					$priority[] = $node;
					Scanner::$been_prioritized[] = $node[ 'path' ];
				}
				foreach( $children as $child ){
					$priority = Scanner::scan_for_pattern( $root, $child, $priority );
				}
				
			}
			return $priority;
		}

		// Checks that a group of children are eligible to become the important pattern
		public static function are_eligible( $nodes ){
			$main_wrap = [];
			foreach( $nodes as $node ){
				$found = -1;
				for( $i = 0; $i < count( $main_wrap ); $i++ ){
					if( $main_wrap[ $i ][ 'pattern' ] == $node[ 'components' ] ){
						$found = $i;
					}
				}
				if( $found > -1 ){
					$main_wrap[ $found ][ 'nodes' ][] = $node;
				}else{
					$main_wrap[] = [ 'pattern' => $node[ 'components' ], 'nodes' => [] ];
				}
			}
			$enough = false;
			foreach( $main_wrap as $obj ){
				if( count( $obj[ 'nodes' ] ) > 3 ){
					//Log::log( "There are ".count( $obj[ 'nodes' ] )." with the pattern <".$obj[ 'pattern' ].">" );
					$enough = true;
				}
			}
			return $enough;
		}

		public static function has_been_prioritized( $path ){
			$found = false;
			foreach( Scanner::$been_prioritized as $prioritized ){
				if( $prioritized == $path ){
					$found = true;
				}
			}
			return $found;
		}


		public static function get_id( $node ){
			$ret_val = false;
			if( get_class( $node[ 'node' ] ) == "DOMElement" && !!$node[ 'node' ]->getAttribute( 'id' ) ){
				$ret_val = $node[ 'node' ]->getAttribute( 'id' );
			}
			return $ret_val;
		}

	}

?>