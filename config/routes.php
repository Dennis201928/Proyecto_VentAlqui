<?php
/**
 * Configuración de rutas
 */

$router->get('/', 'HomeController@index');
$router->get('/venta', 'ProductController@venta');
$router->get('/alquiler', 'ProductController@alquiler');
$router->get('/producto/:id', 'ProductController@show');
$router->get('/contacto', 'ContactController@index');
$router->post('/contacto', 'ContactController@index');
$router->get('/quienes-somos', 'HomeController@quienesSomos');

$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');

$router->get('/mi-perfil', 'UserController@profile', ['auth']);
$router->get('/mis-alquileres', 'RentalController@index', ['auth']);
$router->get('/carrito', 'CartController@index', ['auth']);
$router->get('/carrito/remove/:id', 'CartController@remove', ['auth']);
$router->get('/checkout', 'CartController@checkout', ['auth']);
$router->post('/checkout', 'CartController@checkout', ['auth']);
$router->get('/alquiler/:id', 'RentalController@showRental', ['auth']);
$router->get('/venta/:id', 'ProductController@showSale', ['auth']);
$router->get('/pedido-exitoso/:id', 'OrderController@success', ['auth']);

$router->get('/recuperar-contrasena', 'AuthController@showForgotPassword');
$router->post('/recuperar-contrasena', 'AuthController@forgotPassword');

$router->get('/admin', 'AdminController@dashboard', ['auth', 'admin']);
$router->get('/admin/productos', 'AdminController@products', ['auth', 'admin']);
$router->get('/admin/productos/create', 'AdminController@addProduct', ['auth', 'admin']);
$router->post('/admin/productos', 'AdminController@createProduct', ['auth', 'admin']);
$router->get('/admin/productos/edit/:id', 'AdminController@editProduct', ['auth', 'admin']);
$router->post('/admin/productos/edit/:id', 'AdminController@editProduct', ['auth', 'admin']);
$router->get('/admin/productos/delete/:id', 'AdminController@deleteProduct', ['auth', 'admin']);
$router->get('/admin/categorias', 'AdminController@categories', ['auth', 'admin']);
$router->post('/admin/categorias', 'AdminController@categories', ['auth', 'admin']);
$router->get('/admin/calendario-alquileres', 'AdminController@rentalCalendar', ['auth', 'admin']);
$router->get('/admin/calendario-ventas', 'AdminController@saleCalendar', ['auth', 'admin']);
