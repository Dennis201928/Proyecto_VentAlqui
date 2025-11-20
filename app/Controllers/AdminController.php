<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Auth;
use App\Models\Rental;

/**
 * Controlador de administración
 */
class AdminController extends Controller {
    
    public function dashboard() {
        $auth = new Auth();
        $product = new Product();
        $category = new Category();
        $rental = new Rental();
        
        // Obtener estadísticas
        $all_products = $product->getProducts();
        $active_products = $product->getProducts(['estado' => 'disponible']);
        $all_categories = $category->getCategories();
        $all_rentals = $rental->getAllRentalsWithFilters([]);
        
        $stats = [
            'total_products' => is_array($all_products) ? count($all_products) : 0,
            'active_products' => is_array($active_products) ? count($active_products) : 0,
            'total_categories' => is_array($all_categories) ? count($all_categories) : 0,
            'total_rentals' => (is_array($all_rentals) && !isset($all_rentals['error'])) ? count($all_rentals) : 0
        ];
        
        $data = [
            'title' => 'Panel de Administración - AlquiVenta',
            'current_user' => $auth->getCurrentUser(),
            'stats' => $stats,
            'message' => $_GET['message'] ?? '',
            'error' => $_GET['error'] ?? ''
        ];
        
        $this->view('admin/dashboard', $data, 'admin');
    }
    
    public function products() {
        $auth = new Auth();
        $product = new Product();
        
        $filters = [];
        if (isset($_GET['tipo'])) {
            $filters['tipo'] = $_GET['tipo'];
        }
        if (isset($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        
        $data = [
            'title' => 'Gestión de Productos - AlquiVenta',
            'current_user' => $auth->getCurrentUser(),
            'products' => $product->getProducts($filters),
            'message' => $_GET['message'] ?? '',
            'error' => $_GET['error'] ?? ''
        ];
        
        $this->view('admin/products', $data, 'admin');
    }
    
    public function addProduct() {
        try {
            $auth = new Auth();
            $category = new Category();
            
            $categories_all = $category->getCategories();
            $categories_maq = $category->getCategories('maquinaria');
            $categories_mat = $category->getCategories('material');
            
            // Verificar si hay errores
            if (isset($categories_all['error'])) {
                $categories_all = [];
            }
            if (isset($categories_maq['error'])) {
                $categories_maq = [];
            }
            if (isset($categories_mat['error'])) {
                $categories_mat = [];
            }
            
            $data = [
                'title' => 'Agregar Producto - AlquiVenta',
                'current_user' => $auth->getCurrentUser(),
                'categories' => $categories_all,
                'categorias_maquinaria' => $categories_maq,
                'categorias_material' => $categories_mat
            ];
            
            $this->view('admin/add-product', $data, 'admin');
        } catch (\Exception $e) {
            $data['error'] = 'Error al cargar el formulario. Por favor, intente más tarde.';
            $this->view('admin/add-product', $data, 'admin');
        } catch (\Error $e) {
            $data['error'] = 'Error fatal al cargar el formulario. Por favor, intente más tarde.';
            $this->view('admin/add-product', $data, 'admin');
        }
    }
    
    public function createProduct() {
        if (!$this->isPost()) {
            $this->redirect('/admin/productos');
        }
        
        $product = new Product();
        $imageUpload = new \App\Helpers\ImageUpload();
        
        try {
            $nombre = trim($this->post('nombre', ''));
            $descripcion = trim($this->post('descripcion', ''));
            $categoria_id = (int)$this->post('categoria_id', 0);
            $precio_venta = (float)$this->post('precio_venta', 0);
            $precio_alquiler_dia = (float)$this->post('precio_alquiler_dia', 0);
            $stock_disponible = (int)$this->post('stock_disponible', 0);
            $stock_minimo = (int)$this->post('stock_minimo', 0);
            $estado = $this->post('estado', 'disponible');
            
            if (empty($nombre) || $categoria_id <= 0) {
                throw new \Exception('Los campos nombre y categoría son obligatorios');
            }
            
            $imagen_principal = null;
            if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] == 0) {
                try {
                    $imagen_principal = $imageUpload->uploadImage($_FILES['imagen_principal'], 'principal');
                    // Verificar que la imagen se haya guardado correctamente
                    if (empty($imagen_principal)) {
                        throw new \Exception('Error: La ruta de la imagen está vacía después de la subida');
                    }
                } catch (\Exception $e) {
                    throw new \Exception('Error al subir la imagen: ' . $e->getMessage());
                }
            } else {
                $error_code = $_FILES['imagen_principal']['error'] ?? 'NO_FILE';
                throw new \Exception('La imagen principal es requerida');
            }
            
            $product_data = [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'categoria_id' => $categoria_id,
                'precio_venta' => $precio_venta,
                'precio_alquiler_dia' => $precio_alquiler_dia,
                'stock_disponible' => $stock_disponible,
                'stock_minimo' => $stock_minimo,
                'imagen_principal' => $imagen_principal,
                'imagenes_adicionales' => [],
                'especificaciones' => '{}',
                'estado' => $estado
            ];
            
            $result = $product->createProduct($product_data);
            
            if ($result['success']) {
                $this->redirect('/admin/productos?message=' . urlencode('Producto creado exitosamente'));
            } else {
                $this->redirect('/admin/productos/create?error=' . urlencode($result['message']));
            }
        } catch (\Exception $e) {
            $this->redirect('/admin/productos/create?error=' . urlencode($e->getMessage()));
        }
    }
    
    public function categories() {
        $auth = new Auth();
        $category = new Category();
        
        // Manejar POST (crear, actualizar, eliminar)
        if ($this->isPost()) {
            $action = $this->post('action', 'create');
            
            if ($action === 'create') {
                $data = [
                    'nombre' => trim($this->post('nombre', '')),
                    'tipo' => $this->post('tipo', ''),
                    'descripcion' => trim($this->post('descripcion', ''))
                ];
                
                $result = $category->createCategory($data);
                
                if ($result['success']) {
                    $this->redirect('/admin/categorias?message=' . urlencode('Categoría creada exitosamente'));
                } else {
                    $this->redirect('/admin/categorias?error=' . urlencode($result['message']));
                }
                return;
            } elseif ($action === 'update') {
                $id = (int)$this->post('category_id', 0);
                $data = [
                    'nombre' => trim($this->post('nombre', '')),
                    'tipo' => $this->post('tipo', ''),
                    'descripcion' => trim($this->post('descripcion', ''))
                ];
                
                $result = $category->updateCategory($id, $data);
                
                if ($result['success']) {
                    $this->redirect('/admin/categorias?message=' . urlencode('Categoría actualizada exitosamente'));
                } else {
                    $this->redirect('/admin/categorias?error=' . urlencode($result['message']));
                }
                return;
            } elseif ($action === 'delete') {
                $id = (int)$this->post('category_id', 0);
                $result = $category->deleteCategory($id);
                
                if ($result['success']) {
                    $this->redirect('/admin/categorias?message=' . urlencode('Categoría eliminada exitosamente'));
                } else {
                    $this->redirect('/admin/categorias?error=' . urlencode($result['message']));
                }
                return;
            }
        }
        
        // GET - Mostrar lista
        $data = [
            'title' => 'Gestión de Categorías - AlquiVenta',
            'current_user' => $auth->getCurrentUser(),
            'categories' => $category->getCategories(),
            'message' => $_GET['message'] ?? '',
            'error' => $_GET['error'] ?? ''
        ];
        
        $this->view('admin/categories', $data, 'admin');
    }
    
    public function editProduct($id) {
        try {
            $auth = new Auth();
            $product = new Product();
            $imageUpload = new \App\Helpers\ImageUpload();
            
            $current_user = $auth->getCurrentUser();
            $product_data = $product->getProductById($id);
            
            if (!$product_data || isset($product_data['error'])) {
                $this->redirect('/admin/productos?error=producto_no_encontrado');
                return;
            }
            
            if ($this->isPost()) {
                $nombre = trim($this->post('nombre', ''));
                $descripcion = trim($this->post('descripcion', ''));
                $categoria_id = (int)$this->post('categoria_id', 0);
                $stock_disponible = (int)$this->post('stock_disponible', 0);
                $estado = $this->post('estado', 'disponible');
                
                if (empty($nombre) || $categoria_id <= 0) {
                    $data = [
                        'title' => 'Editar Producto',
                        'current_user' => $current_user,
                        'product' => $product_data,
                        'categorias_maquinaria' => $product->getCategories('maquinaria'),
                        'categorias_material' => $product->getCategories('material'),
                        'error' => 'Los campos nombre y categoría son obligatorios'
                    ];
                    $this->view('admin/edit-product', $data, 'admin');
                    return;
                }
                
                $imagen_principal = $product_data['imagen_principal'];
                
                if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] == 0) {
                    try {
                        $imagen_principal = $imageUpload->uploadImage($_FILES['imagen_principal'], 'principal');
                        // Verificar que la imagen se haya guardado correctamente
                        if (empty($imagen_principal)) {
                            throw new \Exception('Error: La ruta de la imagen está vacía después de la subida');
                        }
                    } catch (\Exception $e) {
                        $data = [
                            'title' => 'Editar Producto',
                            'current_user' => $current_user,
                            'product' => $product_data,
                            'categorias_maquinaria' => $product->getCategories('maquinaria'),
                            'categorias_material' => $product->getCategories('material'),
                            'error' => 'Error al subir imagen: ' . $e->getMessage()
                        ];
                        $this->view('admin/edit-product', $data, 'admin');
                        return;
                    }
                }
                
                $update_data = [
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'categoria_id' => $categoria_id,
                    'precio_venta' => $product_data['precio_venta'] ?? 0,
                    'precio_alquiler_dia' => $product_data['precio_alquiler_dia'] ?? 0,
                    'stock_disponible' => $stock_disponible,
                    'stock_minimo' => $product_data['stock_minimo'] ?? 0,
                    'imagen_principal' => $imagen_principal,
                    'imagenes_adicionales' => [],
                    'especificaciones' => $product_data['especificaciones'] ?? '{}',
                    'estado' => $estado
                ];
                
                $result = $product->updateProduct($id, $update_data);
                
                if ($result['success']) {
                    $this->redirect('/admin/productos?message=' . urlencode('Producto actualizado exitosamente'));
                } else {
                    $data = [
                        'title' => 'Editar Producto',
                        'current_user' => $current_user,
                        'product' => $product_data,
                        'categorias_maquinaria' => $product->getCategories('maquinaria'),
                        'categorias_material' => $product->getCategories('material'),
                        'error' => $result['message']
                    ];
                    $this->view('admin/edit-product', $data, 'admin');
                }
                return;
            }
            
            $categories_maq = $product->getCategories('maquinaria');
            $categories_mat = $product->getCategories('material');
            
            // Verificar si hay errores
            if (isset($categories_maq['error'])) {
                $categories_maq = [];
            }
            if (isset($categories_mat['error'])) {
                $categories_mat = [];
            }
            
            $data = [
                'title' => 'Editar Producto',
                'current_user' => $current_user,
                'product' => $product_data,
                'categorias_maquinaria' => $categories_maq,
                'categorias_material' => $categories_mat
            ];
            
            $this->view('admin/edit-product', $data, 'admin');
        } catch (\Exception $e) {
            $auth = new Auth();
            $data = [
                'title' => 'Editar Producto',
                'current_user' => $auth->getCurrentUser(),
                'error' => 'Error al cargar el producto. Por favor, intente más tarde.',
                'categorias_maquinaria' => [],
                'categorias_material' => []
            ];
            $this->view('admin/edit-product', $data, 'admin');
        } catch (\Error $e) {
            $auth = new Auth();
            $data = [
                'title' => 'Editar Producto',
                'current_user' => $auth->getCurrentUser(),
                'error' => 'Error fatal al cargar el producto. Por favor, intente más tarde.',
                'categorias_maquinaria' => [],
                'categorias_material' => []
            ];
            $this->view('admin/edit-product', $data, 'admin');
        }
    }
    
    public function deleteProduct($id) {
        $product = new Product();
        
        $result = $product->deleteProduct($id);
        
        if ($result['success']) {
            $this->redirect('/admin/productos?message=' . urlencode('Producto eliminado exitosamente'));
        } else {
            $this->redirect('/admin/productos?error=' . urlencode($result['message']));
        }
    }
    
    public function rentalCalendar() {
        $auth = new Auth();
        $product = new Product();
        $rental = new Rental();
        
        $filters = [
            'categoria_id' => $this->get('categoria_id') ? (int)$this->get('categoria_id') : null,
            'producto_id' => $this->get('producto_id') ? (int)$this->get('producto_id') : null,
            'producto_nombre' => $this->get('producto_nombre') ? trim($this->get('producto_nombre')) : null,
            'estado' => $this->get('estado')
        ];
        
        $categories = $product->getCategories();
        $products = [];
        
        if ($filters['categoria_id']) {
            $products = $product->getProducts(['categoria_id' => $filters['categoria_id']]);
        } else {
            $products = $product->getProducts(['limit' => 100]);
        }
        
        $rentals = $rental->getAllRentalsWithFilters($filters);
        if (isset($rentals['error'])) {
            $rentals = [];
        }
        
        $data = [
            'title' => 'Calendario de Alquileres',
            'current_user' => $auth->getCurrentUser(),
            'categories' => $categories,
            'products' => $products,
            'rentals' => $rentals,
            'filters' => $filters
        ];
        
        $this->view('admin/rental-calendar', $data, 'admin');
    }
}

