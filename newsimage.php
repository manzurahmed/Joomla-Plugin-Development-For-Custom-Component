<?php
/*
 * @copyright Copyright (C) 2018 Manzur Ahmed
 * @license : Commercial
 * @Website : http://www.webtechriser.com
 */
 
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die;
 
jimport( 'joomla.plugin.plugin' );
 
class plgDeshtvshortcodesNewsImage extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	
	function onNewsImageDisplay( &$item ) {
		
		//https://stackoverflow.com/questions/1445506/get-content-between-two-strings-php
		$occurrences = substr_count( $item->fulltext, '[newsimage]' );

		if( $occurrences > 0) {

			$media_pos[] = [];
			$counter = 0;
			$prevPos = 0;

			for( $counter = 0; $counter < $occurrences; $counter++) {

				$firstPos = strpos( $item->fulltext, '[newsimage]', $prevPos ) + strlen('[newsimage]');
				$endingPos = strpos( $item->fulltext, '[/newsimage]', $prevPos + strlen('[newsimage]'));
				//
				$media_id = (int) substr( $item->fulltext, $firstPos, $endingPos - $firstPos );

				// Show the Media Image
				try {

					$db = JFactory::getDbo();
					$query = $db->getQuery( true );
					
					// Media
					$query
						->select(
							array(
								'a.id', 'a.title', 'a.photo', 'a.thumb', 'a.mediatype'
							)
						)
						->from( $db->quoteName('#__adtv_medias', 'a') )
						->where( $db->qn('a.id').' = '. $media_id );
					// Gallery
					$query->select('b.yearfolder, b.relativepath');
					$query->leftJoin($db->quoteName('#__adtv_mediagallery', 'b'). ' ON b.id = a.galleryid');
					
					$db->setQuery( $query );
					$media = $db->loadObject();

					$imageHTML = '';
					$imageHTML .= '<div class="_air-load-image mb20px mt20px">';
					$imageHTML .= '      <img src="' . JUri::root(). OptionsHelper::MediaFolderRel() . $media->yearfolder . '/' . $media->relativepath . (!empty($media->relativepath)?'/':'') . $media->photo . '" width="633" height="340" alt="'. $media->title .'" />';
					$imageHTML .= '<p class="caption"><span>' . $media->title . '</span></p>';
					$imageHTML .= '</div>';

					$item->fulltext = str_replace( '[newsimage]'.$media_id.'[/newsimage]', $imageHTML, $item->fulltext );
				}
				catch (Exception $ex) {
					$this->setError($ex);
				}

				// Fix next starting position for "strpos" function
				$prevPos = $firstPos;
				$media_pos[] = [$firstPos - strlen('[newsimage]'), $endingPos + strlen('[/newsimage]')];
			}
		}
		
		return $item;
	}
}