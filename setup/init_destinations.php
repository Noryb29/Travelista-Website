<?php
require_once '../config/config.php';

// Create destinations directory if it doesn't exist
$destinationsDir = '../assets/images/destinations';
if (!file_exists($destinationsDir)) {
    mkdir($destinationsDir, 0777, true);
    echo "Created destinations directory\n";
}

// Create the destinations table
$createTableSQL = file_get_contents('../database/create_tables.sql');
try {
    $pdo->exec($createTableSQL);
    echo "Created destinations table\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}

// Create the hotels table
$createTableSQL = file_get_contents('../database/create_tables.sql');
try {
    $pdo->exec($createTableSQL);
    echo "Created hotels table\n";
} catch (PDOException $e) {
    echo "Error creating hotels table: " . $e->getMessage() . "\n";
}

// Function to fetch image from URL
function fetchImage($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return $data;
    }
    return false;
}

// Read and parse the inserts.sql file
$insertsSQL = file_get_contents('../database/inserts.sql');

// Insert destinations
preg_match_all("/INSERT INTO destinations.*?VALUES(.*?);/s", $insertsSQL, $destMatches);
if (!empty($destMatches[1][0])) {
    preg_match_all("/\((.*?)\),/s", $destMatches[1][0], $matches);
    $stmt = $pdo->prepare("INSERT INTO destinations (destination_name, destination_img, destination_desc) VALUES (?, ?, ?)");
    foreach ($matches[1] as $match) {
        preg_match_all("/'([^']+)'/", $match, $values);
        if (count($values[1]) === 3) {
            $name = $values[1][0];
            $img_url = $values[1][1];
            $desc = $values[1][2];
            $imageData = fetchImage($img_url);
            if ($imageData !== false) {
                try {
                    $stmt->execute([$name, $imageData, $desc]);
                    echo "Inserted destination: {$name}\n";
                } catch (PDOException $e) {
                    echo "Error inserting {$name}: " . $e->getMessage() . "\n";
                }
            } else {
                echo "Failed to fetch image for {$name}\n";
            }
        }
    }
}

// Insert hotels
preg_match_all("/INSERT INTO hotels.*?VALUES(.*?);/s", $insertsSQL, $hotelMatches);
if (!empty($hotelMatches[1][0])) {
    preg_match_all("/\((.*?)\),/s", $hotelMatches[1][0], $matches);
    $stmt = $pdo->prepare("INSERT INTO hotels (hotel_name, hotel_location, star_rating, price, hotel_img) VALUES (?, ?, ?, ?, ?)");
    foreach ($matches[1] as $match) {
        preg_match_all("/'([^']+)'|([0-9]+\.[0-9]+)|([0-9]+)/", $match, $values);
        // $values[0] will contain all matches in order
        // hotel_name, hotel_location, star_rating, price, hotel_img
        $flat = array_values(array_filter(array_map(function($v){ return trim($v, "',"); }, $values[0]), function($v){ return $v !== ''; }));
        if (count($flat) === 5) {
            $name = $flat[0];
            $location = $flat[1];
            $star = (int)$flat[2];
            $price = (float)$flat[3];
            $img_url = $flat[4];
            $imageData = fetchImage($img_url);
            if ($imageData !== false) {
                try {
                    $stmt->execute([$name, $location, $star, $price, $imageData]);
                    echo "Inserted hotel: {$name}\n";
                } catch (PDOException $e) {
                    echo "Error inserting hotel {$name}: " . $e->getMessage() . "\n";
                }
            } else {
                echo "Failed to fetch image for hotel {$name}\n";
            }
        }
    }
}

echo "Setup complete!\n"; 