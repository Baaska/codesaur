<?php
defined('DEVELOPMENT') || define('DEVELOPMENT', 'v1.2013.04.08');

require_once 'cdn/Application.php';

use cdn\Application;

$BackendApp = new Application();

$BackendApp->router->map('', 'someController:indexAction', array('methods' => 'GET'));
 
$BackendApp->router->map('/phpinfo/', 'phpinfo.php', array('methods' => 'GET'));
$BackendApp->router->map('/login/:id',  array('controller' => 'adminController', 'action' => 'loginAction'), array('name' => 'login'));
$BackendApp->router->map('/users/', 'users#create', array('methods' => 'POST', 'name' => 'users_create'));
$BackendApp->router->map('/users/', 'users#list', array('methods' => 'GET', 'name' => 'users_list'));
$BackendApp->router->map('/users/:id/edit/', 'users#edit', array('methods' => 'GET', 'name' => 'users_edit', 'filters' => array('id' => '(\d+)')));
$BackendApp->router->map('/contact/', array('controller' => 'somesController', 'action' => 'contactAction'), array('name' => 'contact'));
$BackendApp->router->map('/blog/:slug', array('c' => 'BlogController', 'a' => 'showAction'));
$BackendApp->router->map('/site-section/:path','some#target', array( 'filters' => array( 'path' => '(.*)') ) );

$route = $BackendApp->router->matchCurrentRequest();

echo "<p>host: ".$BackendApp->request->getHost()."</p>";
echo "<p>script: ".$BackendApp->request->getScript()."</p>";
echo "<p>url: ".$BackendApp->request->getUrl()."</p>";
echo "<p>url_clean: ".$BackendApp->request->getCleanUrl()."</p>";
echo "<p>base_http: ".$BackendApp->request->getBaseHttp()."</p>";
echo "<p>base_path: ".$BackendApp->request->getBasePath()."</p>";
echo "<p>method: ".$BackendApp->request->getMethod()."</p>";
echo "<p>is secure?: ".(($BackendApp->request->isSecure()) ? "TRUE" : "FALSE") ."</p>";
?>
<h3>Current URL & HTTP method would route to: </h3>
<?php if($route) { ?>
	<strong>Url:</strong>   
	<pre><?php var_dump($route->get_url()); ?></pre>
	<strong>Name:</strong>
	<pre><?php var_dump($route->get_name()); ?></pre>
	<strong>Target:</strong>
	<pre><?php var_dump($route->get_target()); ?></pre>
	<strong>Parameters:</strong>
	<pre><?php var_dump($route->get_parameters()); ?></pre>
	<strong>Methods:</strong>
	<pre><?php var_dump($route->get_methods()); ?></pre>
	<strong>Filters:</strong>
	<pre><?php var_dump($route->get_filters()); ?></pre>
<?php } else { ?>
	<pre>No route matched.</pre>
<?php } ?>

<h3>Try out these URL's.</h3>
<p><a href="<?php echo $BackendApp->router->generate('users_edit', array('id' => 5)); ?>"><?php echo $BackendApp->router->generate('users_edit', array('id' => 5)); ?></a></p>
<p><a href="<?php echo $BackendApp->router->generate('contact'); ?>"><?php echo $BackendApp->router->generate('contact'); ?></a></p>
<p><form action="" method="POST"><input type="submit" value="POST request to current URL" /></form></p>
<p><form action="" method="PUT"><input type="submit" value="PUT request to current URL" /></form></p>
<p><form action="" method="DELETE"><input type="submit" value="DELETE request to current URL" /></form></p>
<p><form action="<?php echo $BackendApp->router->generate('users_create'); ?>" method="POST"><input type="submit" value="POST request to <?php echo $BackendApp->router->generate('users_create'); ?>" /></form></p>
<p><a href="<?php echo $BackendApp->router->generate('usserss_create'); ?>">GET request to <?php echo $BackendApp->router->generate('users_create'); ?></p>