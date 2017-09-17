<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Routes
$app->get('/', function (Request $request, Response $response) {
    $mapper = new BookingMapper($this->db);
    $available_hours = $mapper->getAvailableHours();
    // Render index view
    return $this->renderer->render($response, 'index.phtml', ['available_hours' => $available_hours, 'router' => $this->router]);
})->setName('home');

$app->get('/bookings[/{id}]', function (Request $request, Response $response, $args) {
    $mapper = new BookingMapper($this->db);
    $bookings = $mapper->getBookings();
    $params = $request->getQueryParams();

    // search
    $search = $params['search'];
    if (!empty($search)) {
        $bookings = array_filter($bookings, function($a) use($search) {
            return (stripos($a->getName(), $search) !== false);
        });
    }

    // sorting
    $sort = $params['sort'];
    $desc = $params['desc'];
    if (isset($sort)) {
        usort($bookings, function($a, $b) use($desc, $sort) {
            $first = isset($desc) ? $b->$sort : $a->$sort;
            $second = isset($desc) ? $a->$sort : $b->$sort;

            if (is_numeric($first) && is_numeric($second)) {
                return $first - $second;
            }
            return strcmp($first, $second);
        });
        if (isset($desc)) {
           unset($params['desc']);
           unset($params['sort']);
        }
    }

    // pagination
    $page = $params['page'];
    $page = (isset($page) && is_numeric($page)) ? $page : 1;
    $per_page = 10;
    $total = count($bookings);
    $start = ($page - 1) * $per_page;
    $rows = array_slice($bookings, $start, $per_page);

    $response = $this->renderer->render($response, 'bookings.phtml', [
        'id' => $args['id'],
        'rows' => $rows,
        'pages' => ceil($total / $per_page),
        'router' => $this->router,
        'params' => $params
    ]);

    return $response;
})->setName('bookings');

$app->post('/bookings/new', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $booking_data = [];
    $booking_data['name'] = filter_var($data['name'], FILTER_SANITIZE_STRING);
    $booking_data['phone'] = filter_var($data['phone'], FILTER_SANITIZE_STRING);
    $booking_data['time'] = filter_var($data['time'], FILTER_SANITIZE_STRING);

    $booking = new Booking($booking_data);
    $booking_mapper = new BookingMapper($this->db);
    $id = $booking_mapper->save($booking);

    $response = $response->withRedirect($this->router->pathFor('bookings', ['id' => $id]));
    return $response;
})->setName('new');
