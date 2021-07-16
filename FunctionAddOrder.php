<?php

/**
 * @param int $evenId
 * @param string $eventDate
 * @param string $ticketAdultPrice
 * @param int $ticketAdultQuantity
 * @param int $ticketKidPrice
 * @param int $ticketKidQuantity
 * @throws Exception
 */
function addOrder(int $evenId, string $eventDate, string $ticketAdultPrice, int $ticketAdultQuantity, int $ticketKidPrice, int $ticketKidQuantity): void
{
    $barcode = generateBarcode();

    if (bookingOrder($evenId, $eventDate, $ticketAdultPrice, $ticketAdultQuantity, $ticketKidPrice, $ticketKidQuantity, $barcode)) {
        $result = orderApprove($barcode);
        if ($result === 'order successfully approved') {
            save($evenId, $eventDate, $ticketAdultPrice, $ticketAdultQuantity, $ticketKidPrice, $ticketKidQuantity, $barcode);
        }
        throw new Exception($result);
    }
    throw new Exception('Something went wrong');

}

/**
 * @return int|null
 */
function generateBarcode(): ?int
{
    $barcode = mt_rand();

    if (validateBarcode($barcode)) {
        return $barcode;
    }

    return generateBarcode();
}

/**
 * @param int $barcode
 * @return bool
 */
function validateBarcode(int $barcode): bool
{
    if (isUniqBarcode($barcode)) {
        return true;
    }
    return false;

}

/**
 * @param int $barcode
 * @return bool
 */
function isUniqBarcode(int $barcode): bool
{
    if (checkBarcodeInDb($barcode)) {
        return true;
    }
    return false;
}

/**
 * @param int $barcode
 * @return bool
 */
function checkBarcodeInDb(int $barcode): bool
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
 * @param string $ticketAdultPrice
 * @param int $ticketAdultQuantity
 * @param int $ticketKidPrice
 * @param int $ticketKidQuantity
 * @param int $barcode
 * @return bool
 */
function bookingOrder(int $evenId, string $eventDate, string $ticketAdultPrice, int $ticketAdultQuantity, int $ticketKidPrice, int $ticketKidQuantity, int $barcode): bool
{
    $response = apiRequest('https://api.site.com/book', [
        'event_id' => $evenId,
        'event_date' => $eventDate,
        'ticket_adult_price' => $ticketAdultPrice,
        'ticket_adult_quantity' => $ticketAdultQuantity,
        'ticket_kid_price' => $ticketKidPrice,
        'ticket_kid_quantity' => $ticketKidQuantity,
        'barcode' => $barcode
    ]);

    if ($response['message'] === 'order successfully booked') {
        return true;
    }
    return false;
}

/**
 * @param int $barcode
 * @return string
 */
function orderApprove(int $barcode): string
{
    $response = apiRequest('https://api.site.com/approve', ['barcode' => $barcode]);

    if ($response['message'] === 'order successfully approved') {
        return $response['message'];
    }
    return $response['error'];
}

/**
 * @param string $url
 * @param array $data
 * @return array
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
 * @param string $ticketAdultPrice
 * @param int $ticketAdultQuantity
 * @param int $ticketKidPrice
 * @param int $ticketKidQuantity
 * @param int $barcode
 */
function save(int $evenId, string $eventDate, string $ticketAdultPrice, int $ticketAdultQuantity, int $ticketKidPrice, int $ticketKidQuantity, int $barcode): void
{
    mysqli_query('INSERT INTO Orders(event_id, event_date, ticket_adult_price, ticket_adult_quantity, ticket_kid_price, ticket_kid_quantity, barcode)
values(' . $evenId . ',' . $eventDate . ',' . $ticketAdultPrice . ',' . $ticketAdultQuantity . ',' . $ticketKidPrice . ',' . $ticketKidQuantity . ',' . $barcode . ');');
}


