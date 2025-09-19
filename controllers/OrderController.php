<?php
// controllers/OrderController.php
class OrderController {
    private $db;
    private $order;
    private $orderDetail;
    private $product;
    private $customer;

    public function __construct() {
        loadClass('Database');
        loadClass('Order');
        loadClass('OrderDetail');
        loadClass('Product');
        loadClass('Customer');

        $database = new Database();
        $this->db = $database->getConnection();
        $this->order = new Order($this->db);
        $this->orderDetail = new OrderDetail($this->db);
        $this->product = new Product($this->db);
        $this->customer = new Customer($this->db);
    }

    /**
     * Crear un nuevo pedido
     */
    public function createOrder($customerNumber, $requiredDate, $orderItems) {
        try {
            // Validar que el cliente existe
            $customerExists = $this->customer->getById($customerNumber);
            if (!$customerExists) {
                throw new Exception("Cliente no encontrado");
            }

            // Validar que hay items en el pedido
            if (empty($orderItems)) {
                throw new Exception("El pedido debe contener al menos un producto");
            }

            // Validar stock antes de crear el pedido
            $stockValidation = $this->validateStock($orderItems);
            if (!$stockValidation['success']) {
                throw new Exception($stockValidation['message']);
            }

            // Crear el pedido
            $orderId = $this->order->create($customerNumber, $requiredDate, $orderItems);

            return [
                'success' => true,
                'order_id' => $orderId,
                'message' => 'Pedido creado exitosamente',
                'data' => [
                    'order_number' => $orderId,
                    'customer_number' => $customerNumber,
                    'items_count' => count($orderItems),
                    'total' => $this->calculateTotal($orderItems)
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 'ORDER_CREATE_ERROR'
            ];
        }
    }

    /**
     * Obtener pedido con detalles
     */
    public function getOrderWithDetails($orderId) {
        try {
            $order = $this->order->getById($orderId);
            if (!$order) {
                throw new Exception('Pedido no encontrado');
            }

            $details = $this->orderDetail->getByOrderId($orderId);
            $customer = $this->customer->getById($order['customer_number']);

            // Calcular totales
            $subtotal = 0;
            $totalQuantity = 0;

            foreach ($details as &$detail) {
                $itemTotal = $detail['quantiy'] * $detail['unit_price'];
                $detail['item_total'] = $itemTotal;
                $subtotal += $itemTotal;
                $totalQuantity += $detail['quantiy'];
            }

            return [
                'success' => true,
                'order' => array_merge($order, [
                    'customer_info' => $customer,
                    'subtotal' => $subtotal,
                    'total_quantity' => $totalQuantity,
                    'items_count' => count($details)
                ]),
                'details' => $details
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 'ORDER_GET_ERROR'
            ];
        }
    }

    /**
     * Obtener todos los pedidos con información básica
     */
    public function getAllOrders($limit = null, $offset = 0) {
        try {
            $orders = $this->order->getAll();

            // Aplicar límite si se especifica
            if ($limit) {
                $orders = array_slice($orders, $offset, $limit);
            }

            // Enriquecer con información adicional
            foreach ($orders as &$order) {
                $details = $this->orderDetail->getByOrderId($order['order_number']);
                $total = 0;
                $itemCount = 0;

                foreach ($details as $detail) {
                    $total += $detail['quantiy'] * $detail['unit_price'];
                    $itemCount += $detail['quantiy'];
                }

                $order['total'] = $total;
                $order['items_count'] = count($details);
                $order['total_quantity'] = $itemCount;
                $order['status'] = $this->getOrderStatus($order);
            }

            return [
                'success' => true,
                'orders' => $orders,
                'count' => count($orders)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 'ORDERS_GET_ERROR'
            ];
        }
    }

    /**
     * Actualizar estado de envío del pedido
     */
    public function updateShippingStatus($orderId, $shippedDate = null) {
        try {
            $shippedDate = $shippedDate ?: date('Y-m-d');

            $query = "UPDATE orders SET shipped = :shipped_date WHERE order_number = :order_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':shipped_date', $shippedDate);
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Estado de envío actualizado',
                    'shipped_date' => $shippedDate
                ];
            } else {
                throw new Exception('Error al actualizar el estado de envío');
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 'SHIPPING_UPDATE_ERROR'
            ];
        }
    }

    /**
     * Obtener estadísticas de pedidos
     */
    public function getOrderStats($startDate = null, $endDate = null) {
        try {
            $whereClause = '';
            $params = [];

            if ($startDate && $endDate) {
                $whereClause = 'WHERE order_date BETWEEN :start_date AND :end_date';
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }

            // Estadísticas básicas
            $query = "SELECT 
                        COUNT(*) as total_orders,
                        COUNT(CASE WHEN shipped IS NOT NULL THEN 1 END) as shipped_orders,
                        COUNT(CASE WHEN shipped IS NULL THEN 1 END) as pending_orders
                      FROM orders $whereClause";

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $basicStats = $stmt->fetch();

            // Pedidos por mes (últimos 6 meses)
            $monthlyQuery = "SELECT 
                               DATE_FORMAT(order_date, '%Y-%m') as month,
                               COUNT(*) as orders_count,
                               SUM(CASE WHEN shipped IS NOT NULL THEN 1 ELSE 0 END) as shipped_count
                             FROM orders 
                             WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                             GROUP BY DATE_FORMAT(order_date, '%Y-%m')
                             ORDER BY month DESC";

            $stmt = $this->db->prepare($monthlyQuery);
            $stmt->execute();
            $monthlyStats = $stmt->fetchAll();

            return [
                'success' => true,
                'stats' => [
                    'basic' => $basicStats,
                    'monthly' => $monthlyStats,
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ]
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 'STATS_ERROR'
            ];
        }
    }

    /**
     * Validar disponibilidad de stock para los items del pedido
     */
    private function validateStock($orderItems) {
        try {
            $errors = [];

            foreach ($orderItems as $item) {
                $stockInfo = $this->product->getStock($item['product_code']);

                if (!$stockInfo) {
                    $errors[] = "Producto código {$item['product_code']} no encontrado";
                    continue;
                }

                if ($stockInfo['stock'] < $item['quantity']) {
                    $errors[] = "Stock insuficiente para {$stockInfo['product']}. Disponible: {$stockInfo['stock']}, Solicitado: {$item['quantity']}";
                }

                if ($item['quantity'] <= 0) {
                    $errors[] = "Cantidad inválida para {$stockInfo['product']}";
                }
            }

            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Errores de validación: ' . implode('; ', $errors),
                    'errors' => $errors
                ];
            }

            return ['success' => true];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en validación de stock: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calcular el total de un conjunto de items
     */
    private function calculateTotal($orderItems) {
        $total = 0;
        foreach ($orderItems as $item) {
            $total += $item['quantity'] * $item['unit_price'];
        }
        return $total;
    }

    /**
     * Obtener el estado de un pedido
     */
    private function getOrderStatus($order) {
        $today = date('Y-m-d');

        if ($order['shipped']) {
            return [
                'code' => 'shipped',
                'label' => 'Enviado',
                'class' => 'success'
            ];
        }

        if ($order['required'] && $order['required'] < $today) {
            return [
                'code' => 'overdue',
                'label' => 'Atrasado',
                'class' => 'danger'
            ];
        }

        if ($order['required'] && $order['required'] == $today) {
            return [
                'code' => 'due_today',
                'label' => 'Vence Hoy',
                'class' => 'warning'
            ];
        }

        return [
            'code' => 'pending',
            'label' => 'Pendiente',
            'class' => 'info'
        ];
    }

    /**
     * Buscar pedidos por diferentes criterios
     */
    public function searchOrders($criteria) {
        try {
            $conditions = [];
            $params = [];

            if (!empty($criteria['order_number'])) {
                $conditions[] = 'o.order_number = :order_number';
                $params[':order_number'] = $criteria['order_number'];
            }

            if (!empty($criteria['customer_name'])) {
                $conditions[] = 'c.name LIKE :customer_name';
                $params[':customer_name'] = '%' . $criteria['customer_name'] . '%';
            }

            if (!empty($criteria['date_from'])) {
                $conditions[] = 'o.order_date >= :date_from';
                $params[':date_from'] = $criteria['date_from'];
            }

            if (!empty($criteria['date_to'])) {
                $conditions[] = 'o.order_date <= :date_to';
                $params[':date_to'] = $criteria['date_to'];
            }

            if (isset($criteria['shipped_status'])) {
                if ($criteria['shipped_status'] === 'shipped') {
                    $conditions[] = 'o.shipped IS NOT NULL';
                } elseif ($criteria['shipped_status'] === 'pending') {
                    $conditions[] = 'o.shipped IS NULL';
                }
            }

            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

            $query = "SELECT o.*, c.name as customer_name, c.email as customer_email
                      FROM orders o 
                      LEFT JOIN customer c ON o.customer_number = c.customer_number 
                      $whereClause
                      ORDER BY o.order_date DESC";

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $orders = $stmt->fetchAll();

            // Enriquecer con detalles adicionales
            foreach ($orders as &$order) {
                $details = $this->orderDetail->getByOrderId($order['order_number']);
                $total = 0;
                foreach ($details as $detail) {
                    $total += $detail['quantiy'] * $detail['unit_price'];
                }
                $order['total'] = $total;
                $order['items_count'] = count($details);
                $order['status'] = $this->getOrderStatus($order);
            }

            return [
                'success' => true,
                'orders' => $orders,
                'count' => count($orders),
                'criteria' => $criteria
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 'SEARCH_ERROR'
            ];
        }
    }

    /**
     * Cancelar un pedido (solo si no ha sido enviado)
     */
    public function cancelOrder($orderId, $reason = '') {
        try {
            // Verificar que el pedido existe y no ha sido enviado
            $order = $this->order->getById($orderId);
            if (!$order) {
                throw new Exception('Pedido no encontrado');
            }

            if ($order['shipped']) {
                throw new Exception('No se puede cancelar un pedido que ya ha sido enviado');
            }

            // Obtener detalles del pedido para restaurar stock
            $details = $this->orderDetail->getByOrderId($orderId);

            $this->db->beginTransaction();
            try {
                // Restaurar stock de productos
                foreach ($details as $detail) {
                    $query = "UPDATE product SET stock = stock + :quantity WHERE product_code = :product_code";
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':quantity', $detail['quantiy'], PDO::PARAM_INT);
                    $stmt->bindParam(':product_code', $detail['product_code'], PDO::PARAM_INT);
                    $stmt->execute();
                }

                // Marcar pedido como cancelado (agregar campo cancelled si no existe)
                $query = "UPDATE orders SET 
                           shipped = NULL, 
                           required = NULL 
                         WHERE order_number = :order_id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
                $stmt->execute();

                // Registrar motivo de cancelación en log (opcional)
                if (!empty($reason)) {
                    $logQuery = "INSERT INTO order_log (order_number, action, reason, created_at) 
                                VALUES (:order_id, 'cancelled', :reason, NOW())";
                    $logStmt = $this->db->prepare($logQuery);
                    $logStmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
                    $logStmt->bindParam(':reason', $reason);
                    // Ejecutar solo si la tabla existe
                    try {
                        $logStmt->execute();
                    } catch (Exception $e) {
                        // Ignorar si la tabla de log no existe
                    }
                }

                $this->db->commit();

                return [
                    'success' => true,
                    'message' => 'Pedido cancelado exitosamente',
                    'restored_items' => count($details)
                ];

            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 'CANCEL_ERROR'
            ];
        }
    }
}
?>