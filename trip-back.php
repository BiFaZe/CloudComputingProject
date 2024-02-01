<?php

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "lampone123";
$dbname = "tripdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to retrieve all records from the "oggetti" table
function getAllOggetti() {
    global $conn;

    $sql = "SELECT * FROM oggetti";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $allRecords = [];
        while ($row = $result->fetch_assoc()) {
            $allRecords[] = $row;
        }
        // Return JSON encoded object
        return json_encode($allRecords);
    } else {
        return json_encode([]); // Return an empty JSON array if no records are found
    }
}

// Function to retrieve a specific record from the "oggetti" table by Inv
function getOggettoById($Inv) {
    global $conn;

    $Inv = $conn->real_escape_string($Inv); // Sanitize the input to prevent SQL injection

    $sql = "SELECT * FROM oggetti WHERE Inv = '$Inv'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $specificRecord = $result->fetch_assoc();
        // Return JSON encoded object directly
        return json_encode($specificRecord);
    } else {
        return json_encode(null); // Return null JSON if no record is found
    }
}

// Function to save data into the "oggetti" table
function saveData($data, $idCompilatore) {
    global $conn;

    $linkSimart = $conn->real_escape_string($data['linkSimart']);
    $lunghezza = $conn->real_escape_string($data['lunghezza']);
    $larghezza = $conn->real_escape_string($data['larghezza']);
    $spessore = $conn->real_escape_string($data['spessore']);
    $selectStatoCons = $conn->real_escape_string($data['selectStatoCons']);
    $selectStatoLav = $conn->real_escape_string($data['selectStatoLav']);
    $selecteEdificio = $conn->real_escape_string($data['selecteEdificio']);
    $selectPartizione = $conn->real_escape_string($data['selectPartizione']);
    $selectTipo = $conn->real_escape_string($data['selectTipo']);
    $selectSottotipo = $conn->real_escape_string($data['selectSottotipo']);
    $inputCollAtt = $conn->real_escape_string($data['inputCollAtt']);
    $note_ricostruttive = $conn->real_escape_string($data['note_ricostruttive']);
    $note_lavorazione = $conn->real_escape_string($data['note_lavorazione']);
    $idCompilatore = $conn->real_escape_string($idCompilatore);

    $sql = "INSERT INTO oggetti (link_simart, lunghezza, larghezza, spessore, stato_di_lavorazione, stato_di_conservazione,
	edificio, partizione, tipo, sottotipo, collocazione_attuale, note_ricostruttive, note_lavorazione, IdCompilatore) VALUES (
        '$linkSimart',
        '$lunghezza',
        '$larghezza',
        '$spessore',
		'$selectStatoLav',
        '$selectStatoCons',
        '$selecteEdificio',
        '$selectPartizione',
        '$selectTipo',
        '$selectSottotipo',
        '$inputCollAtt',
        '$note_ricostruttive',
        '$note_lavorazione',
        '$idCompilatore'
    )";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success', 'message' => 'Data saved successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error saving data']);
    }
}

// Handle AJAX request
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'getAllOggetti':
            echo getAllOggetti();
            break;
        case 'getOggettoById':
            $Inv = isset($_GET['Inv']) ? $_GET['Inv'] : '';
            echo getOggettoById($Inv);
            break;
        case 'saveData':
            // Get the JSON data sent from the client
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Check if IdCompilatore is present in the data
            if (isset($data['IdCompilatore'])) {
                $idCompilatore = $data['IdCompilatore'];
                // Call saveData function with the provided data and IdCompilatore
                saveData($data, $idCompilatore);
            } else {
                // Handle the case when IdCompilatore is not present
                echo json_encode(['status' => 'error', 'message' => 'IdCompilatore is missing']);
            }
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} else {
    echo json_encode(['error' => 'Action not specified']);
}

// Close the database connection
$conn->close();
?>
