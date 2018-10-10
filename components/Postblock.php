<?php

namespace Enovision\Rocktober\Components;

use Db;
use Carbon\Carbon;
use Cms\Classes\Theme;
use Cms\Classes\Page;
use RainLab\Blog\Models\Category as BlogCategoryModel;
use Enovision\Rocktober\Models\Post as PostModel;
use Enovision\Rocktober\classes\php\Helper;
use Illuminate\Support\Facades\App;

class Postblock extends \Cms\Classes\ComponentBase {

	public $posts;
	public $title;
	public $partial;
	public $pageParam;
	public $noPostsMessage;
	public $category;
	public $slugCategory;
	public $currentPage = 1;

	/**
	 * Reference to the page name for linking to posts.
	 * @var string
	 */
	public $postPage;

	/**
	 * Reference to the page name for linking to categories.
	 * @var string
	 */
	public $categoryPage;

	/**
	 * The componentDetails method is required.
	 * The method should return an array with two keys: name and description.
	 * The name and description are display in the CMS back-end user interface.
	 *
	 * @return array
	 */
	public function componentDetails() {
		return [
			'name'        => 'enovision.rocktober::lang.postblock.name',
			'description' => 'enovision.rocktober::lang.postblock.description'
		];
	}

	/**
	 * Inside the component you can read the property value with the property method:
	 * $this->property('title');
	 *
	 * or:
	 * $this->property('title', 'Fallback title');
	 *
	 * or all props at once:
	 * $this->getProperties();
	 *
	 * and in Twig:
	 * {{ __SELF__.property('title') }}
	 *
	 * @return array
	 */
	public function defineProperties() {

		$categoryItems = BlogCategoryModel::lists( 'name', 'id' );

		return [
			'title'             => [
				'description' => 'Title',
				'title'       => 'Title',
				'default'     => '',
				'type'        => 'string'
			],
			'partial'           => [
				'title'   => 'Partial',
				'type'    => 'dropdown',
				'required' => '1'
			],
			'postsPerPage'      => [
				'title'             => 'rainlab.blog::lang.settings.posts_per_page',
				'type'              => 'string',
				'validationPattern' => '^[0-9]+$',
				'validationMessage' => 'rainlab.blog::lang.settings.posts_per_page_validation',
				'default'           => '4',
			],
			'numberOfColumnsXs' => [
				'title'             => 'enovision.rocktober::lang.settings.number_of_columns_xs',
				'description'       => 'enovision.rocktober::lang.settings.number_of_columns',
				'type'              => 'string',
				'validationPattern' => '^[0-9]+$',
				'default'           => '1',
				'validationMessage' => 'rainlab.blog::lang.settings.posts_per_page_validation',
				'group'             => 'Responsive Columns'
			],
			'numberOfColumnsSm' => [
				'title'             => 'enovision.rocktober::lang.settings.number_of_columns_sm',
				'description'       => 'enovision.rocktober::lang.settings.number_of_columns',
				'type'              => 'string',
				'validationPattern' => '^[0-9]+$',
				'validationMessage' => 'rainlab.blog::lang.settings.posts_per_page_validation',
				'group'             => 'Responsive Columns'
			],
			'numberOfColumnsMd' => [
				'title'             => 'enovision.rocktober::lang.settings.number_of_columns_md',
				'description'       => 'enovision.rocktober::lang.settings.number_of_columns',
				'type'              => 'string',
				'validationPattern' => '^[0-9]+$',
				'validationMessage' => 'rainlab.blog::lang.settings.posts_per_page_validation',
				'group'             => 'Responsive Columns'
			],
			'numberOfColumnsLg' => [
				'title'             => 'enovision.rocktober::lang.settings.number_of_columns_lg',
				'description'       => 'enovision.rocktober::lang.settings.number_of_columns',
				'type'              => 'string',
				'validationPattern' => '^[0-9]+$',
				'validationMessage' => 'rainlab.blog::lang.settings.posts_per_page_validation',
				'group'             => 'Responsive Columns'
			],
			'numberOfColumnsXl' => [
				'title'             => 'enovision.rocktober::lang.settings.number_of_columns_xl',
				'description'       => 'enovision.rocktober::lang.settings.number_of_columns',
				'type'              => 'string',
				'validationPattern' => '^[0-9]+$',
				'validationMessage' => 'rainlab.blog::lang.settings.posts_per_page_validation',
				'default'           => '4',
				'group'             => 'Responsive Columns'
			],

			'isArchivePage'     => [
				'title'       => 'enovision.rocktober::lang.settings.is_archive_page',
				'description' => 'enovision.rocktober::lang.settings.is_archive_page_description',
				'type'        => 'checkbox',
				'default'     => false,
				'group'       => 'Archive Page'
			],
			'categoryFilter'    => [
				'title'       => 'Category Filter',
				'description' => 'enovision.rocktober::lang.settings.is_archive_page_description',
				'type'        => 'string',
				'default'     => '{{ :slug }}',
				'group'       => 'Archive Page'
			],
			'usePaging'         => [
				'title'       => 'enovision.rocktober::lang.settings.use_paging',
				'description' => 'enovision.rocktober::lang.settings.use_paging_description',
				'type'        => 'checkbox',
				'default'     => false,
				'group'       => 'Archive Page'
			],
			'pageNumber'        => [
				'title'       => 'rainlab.blog::lang.settings.posts_pagination',
				'description' => 'rainlab.blog::lang.settings.posts_pagination_description',
				'type'        => 'string',
				'default'     => '{{ :page }}',
				'group'       => 'Archive Page'
			],
			'noPostsMessage'    => [
				'title'             => 'rainlab.blog::lang.settings.posts_no_posts',
				'description'       => 'rainlab.blog::lang.settings.posts_no_posts_description',
				'type'              => 'string',
				'default'           => 'No posts found',
				'showExternalParam' => false
			],
			'sortOrder'         => [
				'title'       => 'rainlab.blog::lang.settings.posts_order',
				'description' => 'rainlab.blog::lang.settings.posts_order_description',
				'type'        => 'dropdown',
				'default'     => 'published_at desc'
			],
			'sortDirection'     => [
				'title'       => 'enovision.rocktober::lang.settings.posts_direction',
				'description' => 'enovision.rocktober::lang.settings.posts_direction_description',
				'type'        => 'dropdown',
				'options'     => [
					''     => 'None',
					'ASC'  => 'Ascending',
					'DESC' => 'Descending'
				],
				'default'     => 'ASC'
			],
			'includeCategories' => [
				'title'       => 'Include Categories',
				'description' => 'Only Posts with selected categories are included in the search result',
				'type'        => 'set',
				'items'       => $categoryItems,
				'group'       => 'Categories'
			],
			'excludeCategories' => [
				'title'       => 'Exclude Categories',
				'description' => 'Posts with selected categories are excluded from the search result',
				'type'        => 'set',
				'items'       => $categoryItems,
				'group'       => 'Categories'
			],
			'offset'            => [
				'title'             => 'Offset',
				'type'              => 'string',
				'default'           => 0,
				'validationPattern' => '^[0-9]+$',
				'validationMessage' => 'Value should be integer.'
			],
			'postPage'          => [
				'title'       => 'rainlab.blog::lang.settings.posts_post',
				'description' => 'rainlab.blog::lang.settings.posts_post_description',
				'type'        => 'dropdown',
				'default'     => 'blog/post',
				'group'       => 'Links'
			]
		];
	}

	public function getCategoryPageOptions() {
		return Page::sortBy( 'baseFileName' )->lists( 'baseFileName', 'baseFileName' );
	}


	public function getPostPageOptions() {
		return Page::sortBy( 'baseFileName' )->lists( 'baseFileName', 'baseFileName' );
	}

	/**
	 * Included categories list
	 * @return array
	 */
	public function getIncludeCategoriesOptions() {
		return BlogCategoryModel::lists( 'name', 'id' );
	}

	/**
	 * Excluded categories list
	 * @return array
	 */
	public function getExcludeCategoriesOptions() {
		return BlogCategoryModel::lists( 'name', 'id' );
	}

	/**
	 * @see RainLab\Blog\Components\Posts::getSortOrderOptions()
	 * @return mixed
	 */
	public function getSortOrderOptions() {
		return PostModel::$allowedSortingOptions;
	}

	public function getPartialOptions() {
		$theme = Theme::getActiveTheme();

		// $path  = dirname( __FILE__, 2 ) . '/components/partials';
		$path = $theme->getPath() . '/partials/postblock';

		if (file_exists($path) === false) {
			return [];
		}

		$files = array_diff( scandir( $path ), array( '.', '..' ) );

		$out = [];
		foreach ( $files as $file ) {
			$out[ $file ] = $file;
		}

		return $out;
	}

	public function onRender() {
		if (is_array($this->property( 'args' ))) {

		}
	}


	/**
	 * This code will be executed when the page or layout is loaded and the component
	 * is attached to it.
	 */
	public function onRun() {
		// you can add assets here !!!
		// $this->addCss('assets/css/forum.css');

		// $fs = App::make( 'Enovision\FilterService' );
		// $fs->add_filter( 'filter_title_caps', [ $this, 'filter_title_caps' ], 10, 1 );

		$this->prepareVars();

		$slugCategory       = $this->property( 'categoryFilter' );
		$this->slugCategory = $this->page['category'] = Helper::getCategoryIdFromSlug( $slugCategory );
		$this->category     = $this->page['category'] = Helper::getCategoryFromSlug( $slugCategory, true );

		$this->posts = $this->page['posts'] = $this->listPosts();
	}

	public function filter_title_caps( $title ) {
		$new = strtoupper( $title );
		return $new;
	}

	protected function prepareVars() {

		$this->pageParam      = $this->page['pageParam'] = $this->paramName( 'pageNumber' );
		$this->noPostsMessage = $this->page['noPostsMessage'] = $this->property( 'noPostsMessage' );
		$this->currentPage    = $this->param( 'page' );

		$this->title           = $this->property( 'title' );
		$this->numberOfColumns = $this->property( 'numberOfColumns' );

		$partial       = empty( $this->property( 'partial' ) ) ? 'default.htm' : $this->property( 'partial' );

		$this->partial = $partial;

		/*
         * Page links
         */

		$this->postPage     = $this->page['postPage'] = $this->property( 'postPage' );
		$this->categoryPage = $this->page['categoryPage'] = $this->property( 'categoryPage' );
	}


	/**
	 * @see RainLab\Blog\Components\Posts::prepareVars()
	 * @return mixed
	 */
	protected function listPosts() {

		$posts = PostModel::isPublished();

		$posts = $posts->setArchivePage( [
			'isArchivePage' => $this->property( 'isArchivePage' ),
			'slugCategory'  => $this->slugCategory,
			'usePaging'     => $this->property( 'usePaging' )
		] )->includeCategories( [
			'includeCategories' => $this->property( 'includeCategories' )
		] )->excludeCategories( [
			'excludeCategories' => $this->property( 'excludeCategories' )
		] )->setOffset( [
			'offset' => $this->property( 'offset' )
		] )->setStart( [
			'currentPage' => $this->currentPage
		] )->setLimit( [
			'limit' => $this->property( 'postsPerPage' )
			//	] )->setPage( [
			//		'page' => 1 //TODO
		] )->with( [
			'categories',
			'featured_images'
			//	] )->getFeaturedImages([
			//		'type' => 'RainLab\Blog\Models\Post'
		] )->listFrontEnd( [
			'sortDirection' => $this->property( 'sortDirection' ),
			'sortOrder'     => $this->property( 'sortOrder' ),
			'perPage'       => $this->property( 'postsPerPage' )
		] );

		/*
         * Add a "url" helper attribute for linking to each post and category
         */
		$posts->each( function ( $post ) {
			$fs = App::make( 'Enovision\FilterService' );

			$post->setUrl( $this->postPage, $this->controller );

			// $post['title'] = $fs->apply_filters('filter_title_caps', $post['title']);

		} );

		return $posts;
	}

	protected function loadCategory() {
		if ( ! $slug = $this->property( 'categoryFilter' ) ) {
			return null;
		}

		$category = new BlogCategory;

		$category = $category->isClassExtendedWith( 'RainLab.Translate.Behaviors.TranslatableModel' )
			? $category->transWhere( 'slug', $slug )
			: $category->where( 'slug', $slug );

		$category = $category->first();

		return $category ?: null;
	}
}