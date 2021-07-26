<?php

/**
 * @param int $evenId
 * @param string $eventDate
 * @param int $ticketAdultPrice
 * @param int $ticketAdultQuantity
 * @param int $ticketKidPrice
 * @param int $ticketKidQuantity
 * @throws Exception
 * Функция добавляет заказ
 */
function addOrder(int $evenId, string $eventDate, int $ticketAdultPrice, int $ticketAdultQuantity, int $ticketKidPrice, int $ticketKidQuantity): void
{
    $barcode = generateUniqBarcode();

    if (bookingOrder($evenId, $eventDate, $ticketAdultPrice, $ticketAdultQuantity, $ticketKidPrice, $ticketKidQuantity, $barcode)) {
        $approve = orderApprove($barcode);
        if ($approve === 'Order successfully approved') {
            save($evenId, $eventDate, $ticketAdultPrice, $ticketAdultQuantity, $ticketKidPrice, $ticketKidQuantity, $barcode);
        }
        throw new Exception($approve);
    }
    throw new Exception('Something went wrong');

}

/**
 * @return string|null
 * Функция генерирует уникальный баркод.Генерирует его до того момента,пока баркод не будет уникальным
 */
function generateUniqBarcode(): ?string
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $barcode = '';

    for ($i = 0; $i < 10; $i++) {
        $barcode = $characters[rand(0, strlen($characters))];
    }

    if (validateBarcode($barcode)) {
        return $barcode;
    }

    return generateUniqBarcode();
}

/**
 * @param string $barcode
 * @return bool
 * Функция проверяет баркод на уникальность
 */
function validateBarcode(string $barcode): bool
{

    if (checkBarcodeInDb($barcode)) {
        return true;
    }
    return false;

}

/**
 * @param string $barcode
 * @return bool
 * Функция проверяет наличие записи с данным баркодом
 */
function checkBarcodeInDb(string $barcode): bool
{
    $searchForMatches = mysqli_query('SELECT barcode FROM Orders WHERE barcode = ' . $barcode . ';');
    $countMatches = mysqli_num_rows($searchForMatches);

    if ($countMatches === 0) {
        return true;
    }
    return false;
}

/**
 * @param int $evenId
 * @param string $eventDate
 * @param int $ticketAdultPrice
 * @param int $ticketAdultQuantity
 * @param int $ticketKidPrice
 * @param int $ticketKidQuantity
 * @param string $barcode
 * @return bool
 * @throws Exception Функция бронирует заказ,либо выбрасывает исключение
 */
function bookingOrder(int $evenId, string $eventDate, int $ticketAdultPrice, int $ticketAdultQuantity, int $ticketKidPrice, int $ticketKidQuantity, string $barcode): bool
{
    $response = apiRequest('https://api.site.com/book?', [
        'event_id' => $evenId,
        'event_date' => $eventDate,
        'ticket_adult_price' => $ticketAdultPrice,
        'ticket_adult_quantity' => $ticketAdultQuantity,
        'ticket_kid_price' => $ticketKidPrice,
        'ticket_kid_quantity' => $ticketKidQuantity,
        'barcode' => $barcode
    ]);

    if ($response['message'] === 'Order successfully booked') {
        return true;
    }
    return throw new Exception($response['error']);
}

/**
 * @param string $barcode
 * @return string
 * @throws Exception
 * Функция подтверждает  заказ принимая его баркод,либо возращает текст ошибки
 */
function orderApprove(string $barcode): string
{
    $response = apiRequest('https://api.site.com/approve?', ['barcode' => $barcode]);

    if ($response['message'] === 'order successfully approved') {
        return $response['message'];
    }
    return $response['error'];
}

/**
 * @param string $url
 * @param array $data
 * @return array
 * Функция отправляет данные к API
 */
function apiRequest(string $url, array $data): array
{
    $curl = curl_init($url);

    setOptionsCurl($curl, $data);

    $response = json_decode(curl_exec($curl));

    curl_close($curl);

    return $response;
}

/**
 * @param CurlHandle $curl
 * @param array $data
 * Функция настраивает curl
 */
function setOptionsCurl(CurlHandle $curl, array $data): void
{
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
}

/**
 * @param int $evenId
 * @param string $eventDate
 * @param int $ticketAdultPrice
 * @param int $ticketAdultQuantity
 * @param int $ticketKidPrice
 * @param int $ticketKidQuantity
 * @param string $barcode
 * Функция сохраняет данные
 */
function save(int $evenId, string $eventDate, int $ticketAdultPrice, int $ticketAdultQuantity, int $ticketKidPrice, int $ticketKidQuantity, string $barcode): void
{
    mysqli_query('INSERT INTO Orders(event_id, event_date, ticket_adult_price, ticket_adult_quantity, ticket_kid_price, ticket_kid_quantity, barcode)
values(' . $evenId . ',' . $eventDate . ',' . $ticketAdultPrice . ',' . $ticketAdultQuantity . ',' . $ticketKidPrice . ',' . $ticketKidQuantity . ',' . $barcode . ');');
}


