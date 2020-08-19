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
if (array_key_exists('testtype', $_GET)){
    $testtype = filter_var($_GET['testtype'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);
} else {
    $testtype="pass";
}


if($testtype=="fail"){
    $gtm = 'GTM-PZLMNV5';
} else {
    $gtm = 'GTM-TXB77QN';
}
$pageURL = (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') ? "https://" : "http://";
$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
$page_url_parts = explode( '?', $pageURL );


?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Panalysis Skills Test - Basic Analytics and tagging</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','<?php print($gtm);?>');</script>
        <!-- End Google Tag Manager -->

		<meta name="DC.contributor" content="Trevor Brown" />
		<link rel="stylesheet" href="assets/css/main2.css" />
		<link rel="stylesheet" type="text/css" href="assets/css/jcarousel.basic.css">

    <link rel="stylesheet" type="text/css" href="assets/css/jcarousel.basic.css">
		
		<!-- Scripts -->
		<script src="assets/js/jquery.min.js"></script>
		<script src="assets/js/jquery.poptrox.min.js"></script>
		<script src="assets/js/skel.min.js"></script>
		<script src="assets/js/main.js"></script>
    <script src="https://kit.fontawesome.com/472ddd287c.js" crossorigin="anonymous"></script>
    <script type="text/javascript" src="assets/js/jquery.jcarousel.js"></script>
    <script type="text/javascript" src="assets/js/jcarousel.basic.js"></script>
  </head>
  <body>
    <div class="container">
        <div class="row">
            <img src="https://www.panalysis.com/wp-content/uploads/2019/07/Panalysis-Logo.png" style="width:245px; height:52px;"/>
        </div>
        <div class="row justify-content-md-center">
            <h1>Test Automation Skills Test</h1>
        </div>
        <div class="row">

                <p>The purpose of this test is to determine how you approach testing in an analytics context.</p>
                <p>There are a number of test cases to perform based on the requirements below.</p>
                <p>To complete this you should understand the Google Analytics measurement protocol relating to <a href="https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#page">Page Tracking</a> and <a href="https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#event">Event Tracking</a>
                <p>There are two separate Google Tag Manager container IDs. One which is working correctly and the other which is not.</p>
                <ul>
                    <li>Working Correctly:<a href="?testtype=pass"><?php echo($page_url_parts[0]."?testtype=pass"); ?></a></li>
                    <li>Not Working Correctly: <a href="?testtype=fail"><?php echo($page_url_parts[0]."?testtype=fail"); ?></a></li>
        </ul>

        </div>
    

        <div class="row">
            <div>
                <h2>1. Standard Google Analytics Tag With Custom Dimension</h2>

                <h3>Requirement</h3>
                <p>When the page loads a request is sent to Google Analytics with the hit type (t=) set to pageview and Custom Dimension #1 (cd1=) value containing the text "Trevor Brown".</p>

                <pre><code>https://www.google-analytics.com/r/collect?...
                    t=pageview...
                    &cd1=Trevor%20Brown&...</pre></code>

            </div>
        </div>
    

        <div class="row">
            <div>
                <h2>2. Social Media, Email and Phone Click Tracking</h2>

                <h3>Requirement</h3>
                <p>When a user clicks on the Twitter, Facebook and Instagram links a Google Analytics request should be sent using the Social Media parameters.</p>
                <p>Parameters are t=social and sn=Twitter or sn=Facebook or sn=Instagram and sa=Share</p>
                <p>Parameters are:</p>
                    <ul>
                        <li>t=social,</li>
                        <li>sn=Twitter or sn=Facebook or sn=Instagram,</li>
                        <li>sa=Share</li>
                    </ul>
                <pre><code>https://www.google-analytics.com/collect?...
                    &t=social&
                    &sn=Twitter
                    &sa=Share&...</code></pre>

                <h2>Email Tracking</h2>
                <h3>Requirement</h3>
                <p>When a user clicks on the Email link a Google Analytics request must be sent that includes</p>
                <p>Parameters are:</p>
                    <ul>
                        <li>t=event,</li>
                        <li>ec=Email,</li>
                        <li>ea=Share by Email,</li>
                        <li>el=Main Contact Email</li>
                    </ul>
                <pre><code>https://www.google-analytics.com/collect?...
                        &t=event...
                        &ec=Email
                        &ea=Share%20by%20Email
                        &el=Main%20Contact%20Email&...</code></pre>

                <h2>Phone Click Tracking</h2>
                <h3>Requirement</h3>
                <p>When a user clicks on the telephone icon link a Google Analytics request must be sent that includes the parameters listed below.</p>
                <p>Parameters are:</p>
                    <ul>
                        <li>t=event,</li>
                        <li>ec=Click to Call,</li>
                        <li>ea=Call,</li>
                        <li>el=Main Contact Number</li>
                    </ul>
                    <pre><code>https://www.google-analytics.com/r/collect?...
                        &t=event...
                        &ec=Click%20to%20Call
                        &ea=Call
                        &el=Main%20Contact%20Number&...</code></pre>

            </div>
        </div>
        

        <div class="row justify-content-md-center">
                <ul class="icons">
                <li><a href="https://twitter.com/" class="icon style2 fa-twitter" id="twitter" data-track="social-media" data-track-social-media-name="Twitter"><span class="label">Twitter</span></a></li>
                <li><a href="https://facebook.com/" class="icon style2 fa-facebook" id="facebook" data-track="social-media" data-track-social-media-name="Facebook"><span class="label">Facebook</span></a></li>
                <li><a href="https://www.instagram.com/" class="icon style2 fa-instagram" id="instagram" data-track="social-media" data-track-social-media-name="Instagram"><span class="label">Instagram</span></a></li>
                <li><a href="mailto:test@test.com" class="icon style2 fa-envelope-o" id="email" data-track="email"  data-track-email-name="Main Contact Email"><span class="label">Email</span></a></li>
                <li><a href="tel:123456890" class="icon style2  fa-phone" id="telephone" data-track="telephone" data-track-telephone-name="Main Contact Number"><span class="label">Telephone</span></a></li>
                </ul>
        </div>




        <div class="row">
            <div>
            <h2>3. Tracking User Interactions with Carousel</h2>
                <h3>Requirement</h3>
                <p>When the user clicks on the left and right arrows a Google Analytics event must be sent that includes that includes the parameters listed below.</p>
                <p>Parameters are:</p>
                    <ul>
                        <li>t=event,</li>
                        <li>ec=Carousel Clicks,</li>
                        <li>ea=Click-Next OR Click-Previous,</li>
                        <li>el=Portfolio [NUMBER] where [NUMBER] is the number of the image in the carousel order</li>
                    </ul>
                <pre><code>https://www.google-analytics.com/collect?...
                    &t=event&...
                    &ec=Carousel%20Clicks
                    &ea=Click-Next
                    &el=Portfolio%202...</pre></code>
            </div>
        </div>

        <div class="row justify-content-md-center">
        <!-- Carousel -->
        <!-- 
            * Note that the examples use HTML5 data attributes
        -->
            
            
            
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
						

    </div>
        </div>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
</html>