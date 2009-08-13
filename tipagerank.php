<?php
/*
Plugin Name: Tecinfor Page Rank
Plugin URI: http://dev.rafaeldohms.com.br/projects/show/tipagerank
Description: This plugin uses the Tecinfor PageRank check service to add a page rank widget to your blog.
Author: Rafael Dohms & Michel Lander Melo
Version: 0.5.1
Author URI: http://www.rafaeldohms.com.br/

@version 0.5.1
*/

class tiPageRank extends WP_Widget
{
	/**
	 * API url
	 * 
	 * @var string
	 */
	private $prUrl = 'http://pagerank.tecinfor.net/api/';
	
	/**
	 * HTML to display image
	 * 
	 * @var string
	 */
	private $prCode = '<img src="[SRC]" />';
	
	/**
	 * PHP4 Compatible constructor
	 */
	public function tiPageRank()
	{
        $this->__construct();
	}
	
	/**
	 * Class Constructor
	 */
	public function __construct()
	{
		$widget_ops = array('classname' => 'widget_tipagerank', 'description' => 'Display your Page Rank' );
        parent::__construct(false, 'Page Rank', $widget_ops);
	}
	
	/**
	 * Show Widget Administration Form and handle values
	 * 
	 * @param $instance
	 */
	public function form($instance) {
        
		//Get available formats
		$http = new WP_Http();
		$encodedData = $http->get('http://pagerank.tecinfor.net/api/formatos/');
		
		//Decode into array
		if (function_exists('json_decode')){
			$data = json_decode($encodedData['body']);
		}else{
			//Fall back on Mixie Json Class
			$moxieClassFile = ABSPATH . WPINC . DIRECTORY_SEPARATOR . "js" . 
			                                    DIRECTORY_SEPARATOR . "tinymce" . 
			                                    DIRECTORY_SEPARATOR . "plugins" . 
			                                    DIRECTORY_SEPARATOR . "spellchecker" . 
			                                    DIRECTORY_SEPARATOR . "classes" . 
			                                    DIRECTORY_SEPARATOR . "utils" . 
			                                    DIRECTORY_SEPARATOR . "JSON.php";
			if (file_exists($moxieClassFile)){
				require_once($moxieClassFile);
				$json = new Moxiecode_JSON();
				$data = $json->decode($encodedData['body']);
			}else{
				//No JSON support 
				$data = array('a' => 1);
			}
		}
		
		//Get Data
		$instance = wp_parse_args( (array) $instance, array( 'title' => 'Page Rank', 'format' => '1') );
        $title = strip_tags($instance['title']);
        $format = strip_tags($instance['format']);
        
        //Initiate a counter for labels
        $i = 0;
?>
            <p>
	            <label for="<?php echo $this->get_field_id('title'); ?>">Title: 
	                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
	                       name="<?php echo $this->get_field_name('title'); ?>" type="text" 
	                       value="<?php echo attribute_escape($title); ?>" />
	            </label>
            </p>
            
            <p>
	            Choose a format: <br/><br/>
	                <?php foreach($data as $sFormat){?>
	                    
	                    <?php $i++;?>
		                <input class="radio" id="<?php echo $this->get_field_id('format'.$i); ?>" 
		                name="<?php echo $this->get_field_name('format'); ?>" type="radio" 
		                value="<?php echo attribute_escape($sFormat); ?>"
		                <?php echo checked($format, $sFormat)?> autocomplete="off" />
		                <label for="<?php echo $this->get_field_id('format'.$i); ?>">
		                  <?php echo $this->getImgCode($sFormat)?><br/>
		                </label>              
	                <?php }?>
	            
            </p>

<?php
		
	}

	/**
	 * Updates saved values to options table
	 * 
	 * @param array $new_instance
	 * @param array $old_instance
	 * 
	 * @return array
	 */
	public function update($new_instance, $old_instance) {
		$instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['format'] = strip_tags($new_instance['format']);
 
        return $instance;
		
	}

	/**
	 * Displays the widget on the blog
	 * 
	 * @param array $args
	 * @param array $instance
	 */
	public function widget($args, $instance) {
		extract($args, EXTR_SKIP);
 
        echo $before_widget;
        $title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
 
        if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
        
        echo "<p>";
        echo $this->getImgCode($instance['format']);
        echo "</p>";
        
        echo $after_widget;
		
	}

	/**
	 * Builds a image tag to display PageRank image
	 * 
	 * @param int $format
	 * @return string
	 */
	private function getImgCode($format = 1){
		$serviceUrl = $this->prUrl;
		$serviceUrl .= '?url='.get_bloginfo('url');
		$serviceUrl .= '&img='.$format;
		$serviceUrl .= '&method=wp';

		return str_replace("[SRC]", $serviceUrl, $this->prCode);
	}
	
    /**
     * Registers the widget
     */
    public static function register()
    {
        register_widget('tiPageRank');
    }
    

}

//Add widget registration to init
add_action('widgets_init', array('tiPageRank', 'register'));