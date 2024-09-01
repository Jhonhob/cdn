<?php
namespace B2\Modules\Common;

class UrlDetails{
    public function parse_url_details($url){
        $url = untrailingslashit( $url);

		if ( empty( $url ) ) {
			return [];
		}

		$remote_url_response = $this->get_remote_url( $url );


		$html_head     = $this->get_document_head( $remote_url_response );
		$meta_elements = $this->get_meta_with_content_elements( $html_head );

		$data = array(
            'title'       => $this->get_title( $html_head ),
            'icon'        => $this->get_icon( $html_head, $url ),
            'description' => $this->get_description( $meta_elements ),
            'image'       => $this->get_image( $meta_elements, $url ),
        );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		return $response;
    }

    public function get_image($meta_elements, $url ){
        $image = $this->get_metadata_from_meta_element(
			$meta_elements,
			'property',
			'(?:og:image|og:image:url)'
		);

		// Bail out if image not found.
		if ( '' === $image ) {
			return '';
		}

		// Attempt to convert relative URLs to absolute.
		$parsed_url = parse_url( $url );
		if ( isset( $parsed_url['scheme'] ) && isset( $parsed_url['host'] ) ) {
			$root_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . '/';
			$image    = \WP_Http::make_absolute_url( $image, $root_url );
		}

		return $image;
    }

    public function get_metadata_from_meta_element($meta_elements, $attr, $attr_value){
        // Bail out if there are no meta elements.
		if ( empty( $meta_elements[0] ) ) {
			return '';
		}

		$metadata = '';
		$pattern  = '#' .
				/*
				 * Target this attribute and value to find the metadata element.
				 *
				 * Allows for (a) no, single, double quotes and (b) whitespace in the value.
				 *
				 * Why capture the opening quotation mark, i.e. (["\']), and then backreference,
				 * i.e \1, for the closing quotation mark?
				 * To ensure the closing quotation mark matches the opening one. Why? Attribute values
				 * can contain quotation marks, such as an apostrophe in the content.
				 */
				$attr . '=([\"\']??)\s*' . $attr_value . '\s*\1' .

				/*
				 * These are the options:
				 * - i : case insensitive
				 * - s : allows newline characters for the . match (needed for multiline elements)
				 * - U means non-greedy matching
				 */
				'#isU';

		// Find the metadata element.
		foreach ( $meta_elements[0] as $index => $element ) {
			preg_match( $pattern, $element, $match );

			// This is not the metadata element. Skip it.
			if ( empty( $match ) ) {
				continue;
			}

			/*
			 * Found the metadata element.
			 * Get the metadata from its matching content array.
			 */
			if ( isset( $meta_elements[2][ $index ] ) && is_string( $meta_elements[2][ $index ] ) ) {
				$metadata = trim( $meta_elements[2][ $index ] );
			}

			break;
		}

		return $metadata;
    }

    public function get_description($meta_elements){
        // Bail out if there are no meta elements.
        if ( empty( $meta_elements[0] ) ) {
            return '';
        }

        $description = $this->get_metadata_from_meta_element(
            $meta_elements,
            'name',
            '(?:description|og:description)'
        );

        // Bail out if description not found.
        if ( '' === $description ) {
            return '';
        }

        return $this->prepare_metadata_for_output( $description );
    }

    public function prepare_metadata_for_output( $metadata ) {
		$metadata = html_entity_decode( $metadata, ENT_QUOTES, get_bloginfo( 'charset' ) );
		$metadata = wp_strip_all_tags( $metadata );
		return $metadata;
	}

    public function get_icon($html, $url ){
        // Grab the icon's link element.
		$pattern = '#<link\s[^>]*rel=(?:[\"\']??)\s*(?:icon|shortcut icon|icon shortcut)\s*(?:[\"\']??)[^>]*\/?>#isU';
		preg_match( $pattern, $html, $element );
		if ( empty( $element[0] ) || ! is_string( $element[0] ) ) {
			return '';
		}
		$element = trim( $element[0] );

		// Get the icon's href value.
		$pattern = '#href=([\"\']??)([^\" >]*?)\\1[^>]*#isU';
		preg_match( $pattern, $element, $icon );
		if ( empty( $icon[2] ) || ! is_string( $icon[2] ) ) {
			return '';
		}
		$icon = trim( $icon[2] );

		// If the icon is a data URL, return it.
		$parsed_icon = parse_url( $icon );
		if ( isset( $parsed_icon['scheme'] ) && 'data' === $parsed_icon['scheme'] ) {
			return $icon;
		}

		// Attempt to convert relative URLs to absolute.
		if ( ! is_string( $url ) || '' === $url ) {
			return $icon;
		}
		$parsed_url = parse_url( $url );
		if ( isset( $parsed_url['scheme'] ) && isset( $parsed_url['host'] ) ) {
			$root_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . '/';
			$icon     = \WP_Http::make_absolute_url( $icon, $root_url );
		}

		return $icon;
    }

    public function get_title($html){
        $pattern = '#<title[^>]*>(.*?)<\s*/\s*title>#is';
		preg_match( $pattern, $html, $match_title );

		if ( empty( $match_title[1] ) || ! is_string( $match_title[1] ) ) {
			return '';
		}

		$title = trim( $match_title[1] );

		return $this->prepare_metadata_for_output( $title );
    }

    public function get_remote_url($url){
        $modified_user_agent = 'WP-URLDetails/' . get_bloginfo( 'version' ) . ' (+' . get_bloginfo( 'url' ) . ')';

		$args = array(
			'limit_response_size' => 150 * KB_IN_BYTES,
			'user-agent'          => $modified_user_agent,
		);

		$args = apply_filters( 'rest_url_details_http_request_args', $args, $url );

		$response = wp_safe_remote_get( $url, $args );

		if ( \WP_Http::OK !== wp_remote_retrieve_response_code( $response ) ) {
			// Not saving the error response to cache since the error might be temporary.
			return new \WP_Error(
				'no_response',
				__( 'URL not found. Response returned a non-200 status code for this URL.' ),
				array( 'status' => \WP_Http::NOT_FOUND )
			);
		}

		$remote_body = wp_remote_retrieve_body( $response );

		if ( empty( $remote_body ) ) {
			return new \WP_Error(
				'no_content',
				__( 'Unable to retrieve body from response at this URL.' ),
				array( 'status' => \WP_Http::NOT_FOUND )
			);
		}

		return $remote_body;
    }

    public function get_document_head($html){
        $head_html = $html;

		// Find the opening `<head>` tag.
		$head_start = strpos( $html, '<head' );
		if ( false === $head_start ) {
			// Didn't find it. Return the original HTML.
			return $html;
		}

		// Find the closing `</head>` tag.
		$head_end = strpos( $head_html, '</head>' );
		if ( false === $head_end ) {
			// Didn't find it. Find the opening `<body>` tag.
			$head_end = strpos( $head_html, '<body' );

			// Didn't find it. Return the original HTML.
			if ( false === $head_end ) {
				return $html;
			}
		}

		// Extract the HTML from opening tag to the closing tag. Then add the closing tag.
		$head_html  = substr( $head_html, $head_start, $head_end );
		$head_html .= '</head>';

		return $head_html;
    }

    public function get_meta_with_content_elements($html){
        $pattern = '#<meta\s' .

				/*
				 * Allows for additional attributes before the content attribute.
				 * Searches for anything other than > symbol.
				 */
				'[^>]*' .

				/*
				* Find the content attribute. When found, capture its value (.*).
				*
				* Allows for (a) single or double quotes and (b) whitespace in the value.
				*
				* Why capture the opening quotation mark, i.e. (["\']), and then backreference,
				* i.e \1, for the closing quotation mark?
				* To ensure the closing quotation mark matches the opening one. Why? Attribute values
				* can contain quotation marks, such as an apostrophe in the content.
				*/
				'content=(["\']??)(.*)\1' .

				/*
				* Allows for additional attributes after the content attribute.
				* Searches for anything other than > symbol.
				*/
				'[^>]*' .

				/*
				* \/?> searches for the closing > symbol, which can be in either /> or > format.
				* # ends the pattern.
				*/
				'\/?>#' .

				/*
				* These are the options:
				* - i : case insensitive
				* - s : allows newline characters for the . match (needed for multiline elements)
				* - U means non-greedy matching
				*/
				'isU';

		preg_match_all( $pattern, $html, $elements );

		return $elements;
    }
}