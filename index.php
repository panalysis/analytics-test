<!DOCTYPE HTML>
<?php
$gtm = filter_var($_GET['gtm'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);
?>
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
					<h2 style="text-align:center;">5. Track clicks on the submit button</h2>
								<p>Track the clicks on the submit button and record the event label as Success when the two fields have a valid value and Fail when they do not.</p>
								<p>A valid value for name is 3 characters or more. A valid value for the email address must be a valid email address using a regular expression.</p>
						<p>Contact Me</p>

						<?php 
						if (isset($_POST['name'])) { 
							print("Thank you for your submission"); 
							print("<script>dataLayer.push({'event':'formSubmit', formName:'Contact Form'});</script>");
						} 
						?> 

						
						<form action="<?php echo($_SERVER['REQUEST_URI']) ?>" method="POST">
							Your Name: <input type="text" name="name"/>
							Your Email: <input type="text" name="email"/>
							<input type="submit" name="go"/>
						</form>
					</footer>

			</div>

	</body>
</html>
