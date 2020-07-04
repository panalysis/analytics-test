<?php

abstract class AbstractAnalytics {

    protected $debug = false;

    public function __construct($debug) {
          $this->debug = $debug;
    }

    /*
    Sends a normal page-view type tracking request to the analytics server
    */
    public function Track($title) {
        if (!method_exists($this, "GetHitRequest")) {
            throw new Exception( "Missing GetHitRequest function");
        }

        $response = $this->_URLPost(
                    $this->getHost(), 
                    $this->GetHitRequest($this->getUrlPath(), 
                                         $title));
        if( $this->debug )
            echo $response;
        return $response;
    }

    /*
    Sends a exception type tracking request to the analytics server
    */
    public function Error($title, $errorcode) {
        if (!method_exists($this, "GetErrorRequest")) {
            throw new Exception( "Missing GetErrorRequest function");
        }

        $response = $this->_URLPost(
                    $this->getHost(), 
                    $this->GetErrorRequest($this->getUrlPath(), 
                                           $title, 
                                           $errorcode));
        if( $this->debug )
            echo $response;
        return $response;
    }

    /*
    Gets the analytics host name (e.g. https://www.google-analytics.com)
    */
    abstract protected function getHost();

    /*
    Gets the full url to the requested resource
    */
    protected function getUrlPath() {
        return 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . 
           '://' . 
           "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    }

    /*
    Gets the user agent attached to the original request
    */
    protected function _getUserAgent() {
        return array_key_exists('HTTP_USER_AGENT', $_SERVER) 
            ? $_SERVER['HTTP_USER_AGENT'] : "";
    }

    /*
    Gets the http referer for the original request
    */
    protected function _getReferer() {
        return array_key_exists('HTTP_REFERER', $_SERVER) 
          ? $_SERVER['HTTP_REFERER'] : "";
    }

    /* 
    Gets the remote ip address for the original request
    */
    protected function _getRemoteIP() {
        return array_key_exists('REMOTE_ADDR', $_SERVER) 
          ? $_SERVER['REMOTE_ADDR'] : "";
    }

    /*
    Performs a POST request of the data in $data_array to the URL in $url
    */
    private function _URLPost($url, $data_array) { 
      // Need to encode spaces, otherwise services such
      // as Google will return 400 bad request!
      $url = str_replace(" ", "%20", $url);

      // Construct the contexts for the POST requests
      $opts = array(
        'https'=>array(
        'method'=>"POST",
        'header'=>
          "Accept: application/json, text/javascript, */*; q=0.01\r\n".
          "Content-type: application/x-www-form-urlencoded; charset=UTF-8\r\n".
          "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.155 Safari/537.36\r\n".
          "Referer: https://api.example.com/\r\n",
        'content' => http_build_query($data_array)
        )
        ,
        'http'=>array(
        'method'=>"POST",
        'header'=>
          "Accept: application/json, text/javascript, */*; q=0.01\r\n".
          "Content-type: application/x-www-form-urlencoded; charset=UTF-8\r\n".
          "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.155 Safari/537.36\r\n".
          "Referer: https://api.example.com/\r\n",
        'content' => http_build_query($data_array)
        )
      );

      $context = stream_context_create($opts);
      $result = null;
      $dh = fopen("$url",'rb', false, $context);
      if( !$dh )
        return null;

      if( $dh !== false )
        $result = stream_get_contents($dh);

      fclose($dh);

      return $result; 
  }
}

class GoogleAnalytics extends AbstractAnalytics {

    protected $google_host = 'https://www.google-analytics.com/collect';
    protected $google_debug_host = 'https://www.google-analytics.com/debug/collect';

    /* 
    The Google Analytics Tracking Id for this property (e.g. UA-XXXXXX-XX)
    */
    protected $trackingId = '';

    /*
    The name of the application, this is sent to the Google servers
    */
    protected $appName = '';

    public function __construct($TrackingID, $ApplicationName, $debug) {
        parent::__construct($debug);

        $this->trackingId = $TrackingID;
        $this->appName = $ApplicationName;
    }

    protected function getHost() {
      if( $this->debug )
          return $this->google_debug_host;
      return $this->google_host;
    }

    private function getCommonDataArray($url, $title){
        // Standard params
        $v   = 1;
        $cid = $this->_ParseOrCreateAnalyticsCookie();

        return array(
            'v'   => $v,
            'tid' => $this->trackingId,
            'cid' => $cid,
            'an'  => $this->appName,
            'dt'  => $title,
            'dl'  => $url, 
            'ua'  => $this->_getUserAgent(),
            'dr'  => $this->_getReferer(),
            'uip' => $this->_getRemoteIP(),
            'av'  => '1.0'
        );
    }

    protected function GetHitRequest($url, $title) {
        // Create the pageview data
        $data = $this->getCommonDataArray($url, $title);
        $data['t'] = 'pageview';

        // Send PageView hit as POST
        return $data;
    }

    protected function GetErrorRequest($url, $title, $errorcode){
        // Create the error data
        $data = $this->getCommonDataArray($url, $title);
        $data['t']   = 'exception';
        $data['exd'] = $errorcode;
        $data['exf'] = '1';

        return $data;
    }

  // Gets the current Analytics session identifier or 
  // creates a new one if it does not exist
    private function _ParseOrCreateAnalyticsCookie() {
      if (isset($_COOKIE['_ga'])) {
          // An analytics cookie is found
          list($version, $domainDepth, $cid1, $cid2) = preg_split('[\.]', $_COOKIE["_ga"], 4);
          $contents = array(
            'version' => $version,
            'domainDepth' => $domainDepth,
            'cid' => $cid1 . '.' . $cid2
          );
          $cid = $contents['cid'];
      } else {
          // no analytics cookie is found. Create a new one
          $cid1 = mt_rand(0, 2147483647);
          $cid2 = mt_rand(0, 2147483647);

          $cid = $cid1 . '.' . $cid2;
          setcookie('_ga', 'GA1.2.' . $cid, time() + 60 * 60 * 24 * 365 * 2, '/');
      }
      return $cid;
    }
}

$gtm = filter_var($_GET['gtm'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);

?>


<!DOCTYPE HTML>
<html>
	<head>
		<title>Panalysis Skills Test - Basic Analytics and tagging</title>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','<?php print($gtm);?>');</script>
<!-- End Google Tag Manager -->

		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="DC.contributor" content="Trevor Brown" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<link rel="stylesheet" type="text/css" href="assets/css/jcarousel.basic.css">
		
		<!-- Scripts -->
		<script src="assets/js/jquery.min.js"></script>
		<script src="assets/js/jquery.poptrox.min.js"></script>
		<script src="assets/js/skel.min.js"></script>
		<script src="assets/js/main.js"></script>
		
		<script type="text/javascript" src="assets/js/jquery.jcarousel.js"></script>
        <script type="text/javascript" src="assets/js/jcarousel.basic.js"></script>
	<script>
		var dataLayer = dataLayer || [];
		dataLayer.push({'topic':'GTM Skills Test'});
	</script>
	</head>
	<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php print($gtm);?>"
	height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
Your GTM ID: <?php print($gtm); ?>
		<!-- Wrapper -->
			<div id="wrapper">

				<!-- Header -->
					<header id="header">
				
						<h1>This page is provided for you to demonstrate your skills with understanding JavaScript, Google Analytics, JQuery and related technologies.</h1>
						<h2>1. Add Google Analytics tags</h2>
						<p>Start by adding in the Google Analytics tracking code for this site which is supplied in the instructions.</p>
						<h2>2. Add Social Media and Event Tracking</h2>
						<p>Add in the relevant Google Analytics code to track the following buttons. Social media buttons should be tracked as social media interactions and the other buttons tracked as events.</p>
						<ul class="icons">
							<li><a href="https://twitter.com/" class="icon style2 fa-twitter" id="twitter" data-track="social-media" data-track-social-media-name="Twitter"><span class="label">Twitter</span></a></li>
							<li><a href="https://facebook.com/" class="icon style2 fa-facebook" id="facebook" data-track="social-media" data-track-social-media-name="Facebook"><span class="label">Facebook</span></a></li>
							<li><a href="https://www.instagram.com/" class="icon style2 fa-instagram" id="instagram" data-track="social-media" data-track-social-media-name="Instagram"><span class="label">Instagram</span></a></li>
							<li><a href="mailto:test@test.com" class="icon style2 fa-envelope-o" id="email" data-track="email"  data-track-email-name="Main Contact Email"><span class="label">Email</span></a></li>
							<li><a href="tel:123456890" class="icon style2 fa-phone" id="telephone" data-track="telephone" data-track-telephone-name="Main Contact Number"><span class="label">Telephone</span></a></li>
						</ul>
					</header>

				<!-- Main -->
					<section id="main">
						<!-- Carousel -->
						<!-- 
							* Note that the examples use HTML5 data attributes
						-->
							
							<section class="carousel">
							<h2 style="text-align:center;">3. Tracking User Interactions with Data</h2>
							<p>Track the clicks on the left and right arrows and record the value in the HTML5 data attribute as the event label.</p>
								<div class="jcarousel-wrapper">
									<div class="jcarousel" data-track="carousel">
										<ul>
											<li><img src="images/fulls/slider1.jpg" width="600" height="400" alt="" data-image-id="Portfolio 1"></li>
											<li><img src="images/fulls/slider2.jpg" width="600" height="400" alt="" data-image-id="Portfolio 2"></li>
											<li><img src="images/fulls/slider3.jpg" width="600" height="400" alt="" data-image-id="Portfolio 3"></li>
											<li><img src="images/fulls/slider4.jpg" width="600" height="400" alt="" data-image-id="Portfolio 4"></li>
											<li><img src="images/fulls/slider5.jpg" width="600" height="400" alt="" data-image-id="Portfolio 5"></li>
											<li><img src="images/fulls/slider6.jpg" width="600" height="400" alt="" data-image-id="Portfolio 6"></li>
										</ul>
									</div>

									<p class="photo-credits">
										Photos by <a href="http://www.mw-fotografie.de">Marc Wiegelmann</a>
									</p>

									<a href="#" class="jcarousel-control-prev">&lsaquo;</a>
									<a href="#" class="jcarousel-control-next">&rsaquo;</a>
				
									<p class="jcarousel-pagination">
					
									</p>
								</div>
							</section>


						<!-- Thumbnails -->
						<h2 style="text-align:center;">4. Tracking Other User Interactions with Data</h2>
								<p>Track the clicks on the images when they display the image as a lightbox and record the value in the HTML5 data attribute as the event label.</p>
							<section class="thumbnails">
								
							<div>
									<a href="images/fulls/01.jpg" data-track="lightbox" data-track-lightbox-name="Lightbox 1">
										<img src="images/thumbs/01.jpg" alt=""  data-image-id="Number 1" />
										<h3>Lorem ipsum dolor sit amet</h3>
									</a>
									<a href="images/fulls/02.jpg" data-track="lightbox" data-track-lightbox-name="Lightbox 2">
										<img src="images/thumbs/02.jpg" alt=""  data-image-id="Number 2" />
										<h3>Lorem ipsum dolor sit amet</h3>
									</a>
								</div>
								<div>
									<a href="images/fulls/03.jpg" data-track="lightbox" data-track-lightbox-name="Lightbox 3">
										<img src="images/thumbs/03.jpg" alt=""  data-image-id="Number 3" />
										<h3>Lorem ipsum dolor sit amet</h3>
									</a>
									<a href="images/fulls/04.jpg" data-track="lightbox" data-track-lightbox-name="Lightbox 4">
										<img src="images/thumbs/04.jpg" alt=""  data-image-id="Number 4" />
										<h3>Lorem ipsum dolor sit amet</h3>
									</a>
									<a href="images/fulls/05.jpg" data-track="lightbox" data-track-lightbox-name="Lightbox 5">
										<img src="images/thumbs/05.jpg" alt=""  data-image-id="Number 5"  />
										<h3>Lorem ipsum dolor sit amet</h3>
									</a>
								</div>
								<div>
									<a href="images/fulls/06.jpg" data-track="lightbox" data-track-lightbox-name="Lightbox 6">
										<img src="images/thumbs/06.jpg" alt="" data-image-id="Number 6" />
										<h3>Lorem ipsum dolor sit amet</h3>
									</a>
									<a href="images/fulls/07.jpg" data-track="lightbox" data-track-lightbox-name="Lightbox 7">
										<img src="images/thumbs/07.jpg" alt=""  data-image-id="Number 7" />
										<h3>Lorem ipsum dolor sit amet</h3>
									</a>
								</div>
							</section>

					</section>

				<!-- Footer -->
					<footer id="footer">
					<h2 style="text-align:center;">5. Track Form Submits</h2>
								<p>Track form submissions and record the event label as Success when the two fields have a valid value and Fail when they do not.</p>
								<p>When the form is submitted a dataLayer.push event is triggered to send data to Google Tag Manager</p>
						<p>Contact Me</p>

						<?php 
						if (isset($_POST['name']) && strlen($_POST['name'])>3 && isset($_POST['email']) && strlen($_POST['email'])>3) { 
							print("Thank you for your submission"); 
							print("<script>dataLayer.push({'event':'formSubmit', 'formStatus':'Success', 'formName':'Contact Form'});</script>");
						}
						elseif (isset($_POST['name']) || isset($_POST['email'])) {
							print("<script>dataLayer.push({'event':'formSubmit', 'formStatus':'Fail', 'formName':'Contact Form'});</script>");
							print("Sorry that form was incomplete.");
						} 
						?> 

						
						<form action="<?php echo($_SERVER['REQUEST_URI']) ?>" method="POST">
							Your Name: <input type="text" name="name"/>
							Your Email: <input type="text" name="email"/>
							<input type="submit" name="go"/>
						</form>
					</footer>

			</div>
<?php
$analytics = new GoogleAnalytics("UA-4077168-15", "My API", false);

$analytics->Track("GTM Test");
?>
	</body>
</html>
