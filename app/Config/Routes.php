<?php namespace Config;

/**
 * --------------------------------------------------------------------
 * URI Routing
 * --------------------------------------------------------------------
 * This file lets you re-map URI requests to specific controller functions.
 *
 * Typically there is a one-to-one relationship between a URL string
 * and its corresponding controller class/method. The segments in a
 * URL normally follow this pattern:
 *
 *    example.com/class/method/id
 *
 * In some instances, however, you may want to remap this relationship
 * so that a different class/function is called than the one
 * corresponding to the URL.
 */

// Create a new instance of our RouteCollection class.
$routes = Services::routes(true);

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
    require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 * The RouteCollection object allows you to modify the way that the
 * Router works, by acting as a holder for it's configuration settings.
 * The following methods can be called on the object to modify
 * the default operations.
 *
 *    $routes->defaultNamespace()
 *
 * Modifies the namespace that is added to a controller if it doesn't
 * already have one. By default this is the global namespace (\).
 *
 *    $routes->defaultController()
 *
 * Changes the name of the class used as a controller when the route
 * points to a folder instead of a class.
 *
 *    $routes->defaultMethod()
 *
 * Assigns the method inside the controller that is ran when the
 * Router is unable to determine the appropriate method to run.
 *
 *    $routes->setAutoRoute()
 *
 * Determines whether the Router will attempt to match URIs to
 * Controllers when no specific route has been defined. If false,
 * only routes that have been defined here will be available.
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need to it be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Custom Routes
 * --------------------------------------------------------------------
 */

// XXX: i would ideally like these controllers to be prefixed with "API/"" however
// that seems to cause an issue with the current version iv CI (as of 02/05/2019)

$routes->get('account/(:any)', 'AccountController::show/$1');
// $routes->post('account', 'AccountController::create');

$routes->get('branches', 'BranchController::index');
$routes->get('branch/(:any)', 'BranchController::show/$1');

$routes->get('customer/(:any)/(:any)', 'CustomerController::show/$1/$2');
$routes->get('customer/(:any)/', 'CustomerController::show/$1');

//////////////
// INVOICES //
//////////////
$routes->get('(:any)/invoices', 'InvoiceController::index/$1');                                 // get a bunch of "recent" invoices
$routes->get('(:any)/invoice/(:any)/month/(:any)', 'InvoiceController::month/$1/$2/$3');        // get all invoices that occur in a given month
$routes->get('(:any)/invoice/(:any)/(:any)/(:any)', 'InvoiceController::search/$1/$2/$3/$4');   // get all invoices between two dates
$routes->get('(:any)/invoice/(:any)/(:any)', 'InvoiceController::search/$1/$2/$3');             // get all invoices after date
$routes->get('(:any)/invoice/(:any)', 'InvoiceController::show/$1/$2');                         // get an actual invoice "lines"

// $routes->get('login', 'LoginController::index');

////////////
// ORDERS //
////////////

// get all orders
$routes->get('(:any)/orders', 'OrderController::index/$1');											// account_number/orders
$routes->get('(:any)/(:any)/orders', 'OrderController::index/$1/$2');								// account_number/branch_code/orders

// get specific order
$routes->get('(:any)/(:any)/order/(:any)', 'OrderController::showWithBranch/$1/$2/$3');				// account_number/branch_code/order/order_number
$routes->get('(:any)/order/(:any)', 'OrderController::show/$1/$2');									// account_number/order/order_number

// search for order
$routes->get('(:any)/(:any)/order_search/(:any)', 'OrderController::searchWithBranch/$1/$2/$3');	// account_number/branch_code/order_search/search_term
$routes->get('(:any)/order_search/(:any)', 'OrderController::search/$1/$2');						// account_number/order_search/search_term

// create...
$routes->post('order', 'OrderController::create');

//////////
// PING //
//////////
$routes->get('ping', 'PingController::index');

//////////////
// PRODUCTS //
//////////////
$routes->get('(:any)/(:any)/product_search/(:any)/(:any)/(:any)', 'ProductController::search/$1/$2/$3/$4/$5');
$routes->get('(:any)/(:any)/product_search/(:any)/(:any)', 'ProductController::search/$1/$2/$3/$4');
$routes->get('(:any)/(:any)/products', 'ProductController::show/$1/$2');

$routes->get('productgroup/(:any)', 'ProductGroupController::show/$1');

$routes->get('purge', 'PurgeController::index');

// $routes->get('quotes', 'QuoteController::index');
// $routes->get('quote/(:any)/(:any)', 'QuoteController::search/$1/$2');
// $routes->get('quote/(:any)', 'QuoteController::show/$1');
$routes->post('quote', 'QuoteController::create');

// $routes->get('sites', 'SiteController::index');
// $routes->post('site', 'SiteController::create');
// $routes->delete('site/(:any)', 'SiteController::delete/$1');

// this route should never be enabled within the repository, it is for debugging only
// $routes->get('test/(:any)', 'TestController::index/$1');

/////////////
// HEALTHY //
/////////////
$routes->get('healthy', 'HealthyController::index');

// examples
// $routes->get('photos',                 'Photos::index');
// $routes->get('photos/new',             'Photos::new');
// $routes->get('photos/(:segment)/edit', 'Photos::edit/$1');
// $routes->get('photos/(:segment)',      'Photos::show/$1');
// $routes->post('photos',                'Photos::create');
// $routes->patch('photos/(:segment)',    'Photos::update/$1');
// $routes->put('photos/(:segment)',      'Photos::update/$1');
// $routes->delete('photos/(:segment)',   'Photos::delete/$1');
