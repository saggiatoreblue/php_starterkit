<?php
/**
 * Generic utility class for Corporate Reports
 *
 * Some basic frequently used functionality
 *
 * @version    0.1.0
 * @link       http://www.corporatereport.com
 * @author     Corporate Reports
 */
class CRIMain {

	//common
    public $pageName;
    public $pageArray;
	public $devMode = 1;

	//routes public
	public $basePath = '/dev_framework';
	public $router;

	//routes private
	private $match;
	private $routes;
	private $customRoutes;
	private $categories;
	private $i;

    /**
     * Constructor
     *
     */
    public function __construct() {
        $this->pageName = basename($_SERVER['PHP_SELF']);
        $this->pageName = explode(".",$this->pageName);
        $this->pageArray = explode("_",$this->pageName[0]);

		/**
		 * Routing initialization
		 * custom routes
		 */
		$this->customRoutes[0] = array('url' => '/', 'target' => 'home.php', 'name' => 'home-route');
		/* Categories */
		$this->categories = array('sustainability', 'communities');
		/* Make it Happen */
		$this->setupRoutes();

    }
    /**
     * Convenience method for rendering footer
     *
     */
    public function footer() {
        $params = array( 'section' => 'footer' );
        $this->includeSection( $params );
    }
    /**
     * Convenience method for rendering header
     *
     */
    public function header() {
        $params = array( 'section' => 'header' );
        $this->includeSection( $params );
    }
    /**
     * Includes all css files
     *
     */
    public function includeCss() {
		$uri = explode('/', $_SERVER['REQUEST_URI']);
		$docroot = 'http://' . $_SERVER['HTTP_HOST'] .'/'. $uri[$this->devMode] .'/';

        foreach( glob('_css/*.css' ) as $file) {
            echo '<link type="text/css" rel="stylesheet" href="'.$docroot.$file.'" />';
        }
    }
    /**
     * Includes all javascript files
     *
     */
    public function includeJs( $level = '' ) {
		$uri = explode('/', $_SERVER['REQUEST_URI']);
		$docroot = 'http://' . $_SERVER['HTTP_HOST'] .'/'. $uri[$this->devMode] .'/';
        foreach( glob('_js/*.js' ) as $file) {
            echo '<script type="text/javascript" src="'.$docroot.$file.'"></script>';
        }
    }
    /**
     * Generic include method
     *
     */
    public function includeSection( $params ) {
        extract($params);
		$cri = $this;
        $level = (!empty($level)) ? $level : '';
        $path = (!empty($directory)) ? $level . $directory : $level . '_includes/';
        include( $path . $section . '.php' );
    }

    /**
     * Renders out a cycle2 slideshow
     *
     * @param  integer    $numSlides  Number of slides
     * @param  string  $type The type of slideshow
     * @param  integer $pager bool to display pager
     * @return string slideshow html
     */
    public function slideShow($numSlides,$type,$pager) {

        $html =  '<div class="slideshow"><ul id="slideshow" class="cycle-slideshow" data-cycle-slides=">li" data-cycle-pager=".cycle-pager">';
        for ($i = 1; $i <= $numSlides; $i++) {
            $html .= '<li class="slide"><div class="image"><img src="_images/'.$this->pageArray[0].'/slideshow/image'.$i.'.jpg" /></div>';

            if($type !=="imageOnly"){
                $html .= '<div class="copyBlock">nascetur ridiculus mus. Etiam feugiat lacus sit amet dui dictum, non consectetur nibh feugiat. Mauris ante nulla, vulputate ac eleifend nec</div>';
            }
            $html .= '</li>';
        }
        $html .= "</ul>";
        if($pager){
            $html .= '<div class="cycle-pager"></div>';
        }
        echo $html;
    }
	/**
	 * Uses match to get the appropriate page content
	 * @include
	 */
	public function pageContent() {
		if($this->match) {
			include($this->match['target']);
		}
		else {
			include('404.php');
		}
	}

	/**
	 * Loops through the available directories
	 * @include
	 */
	private function searchDirs() {
		foreach($this->categories as $category) {
			$this->routeFactory($category);
		}
	}
	/**
	 * Recursively builds the routes array allowing for infinite subfolders
	 * @param string
	 * @return false to exit cycle
	 */
	private function routeFactory($cat) {

		//this needs to be reworked
		$globFiles = glob($cat.'/*.php', GLOB_BRACE);
		$globDirs = glob($cat.'/*', GLOB_ONLYDIR);

		foreach($globFiles as $url) {
			if(is_file($url)) {
				 $this->i++;
				 $path = str_replace('.php', '', $url);
				 $name = str_replace('/', '-', $path);
				 $path = str_replace('index', '', $path);
				 $this->routes[$this->i] = array('url' => '/'.$path, 'target' => $url, 'name' => $name);
			}

		}
		foreach($globDirs as $dir) {
			$this->routeFactory($dir);
		}
	}
	/**
	 * Sets up the default routing system
	 * @setter the match property
	 */
	private function setupRoutes() {
		include('AltoRouter.php');
		$this->router = new AltoRouter();
		$this->router->setBasePath($this->basePath);
		$this->searchDirs();

		$this->routes = array_merge($this->routes, $this->customRoutes);

		foreach($this->routes as $route) {

			$url    = $route['url'];
			$target = $route['target'];
			$name   = $route['name'];

			$this->router->map('GET',$url, $target, $name);
		}

		//match the routes
		$this->match = $this->router->match();
	}

}
$cri = new CRIMain();