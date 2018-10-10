<?php
/**
 * Created by PhpStorm.
 * User: jvandemerwe
 * Date: 9/23/18
 * Time: 1:59 PM
 */

namespace Enovision\Rocktober\classes\php;

use RainLab\Blog\Models\Category as BlogCategory;


abstract class Helper {

	static function getCategoryFromSlug( $slug, $returnObject = false ) {
		if ( ! isset( $slug ) || $slug === '' ) {
			return null;
		}

		$category = new BlogCategory;

		$category = $category->isClassExtendedWith( 'RainLab.Translate.Behaviors.TranslatableModel' )
			? $category->transWhere( 'slug', $slug )
			: $category->where( 'slug', $slug );

		$category = $category->first();

		if ( $returnObject === true && $category !== null ) {
			$category = (object) $category;
		}

		return $category ?: null;
	}

	static function getCategoryIdFromSlug( $slug ) {
		$category = self::getCategoryFromSlug( $slug );

		return isset( $category ) ? $category->id : null;
	}

	static function getCategoryNameFromSlug( $slug ) {
		$category = self::getCategoryFromSlug( $slug );

		return isset( $category ) ? $category->name : null;
	}

	static function getUrl( $file = null ) {
		$url = str_replace( "\\", '/', "http://" . $_SERVER['HTTP_HOST'] . substr( $file, strlen( $_SERVER['DOCUMENT_ROOT'] ) ) );
		return $url;
	}

}