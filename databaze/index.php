<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style.css">
    <script src="javascript.js"></script>
</head>
<body>
    
</body>
</html>
<?php
    // V případě nějakých errorů ho chci vypsat
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Načítám z http://test.qvamp.eu data
    $datasource = file_get_contents('http://test.qvamp.eu');
    $data = json_decode($datasource, true);
    $db = new PDO('sqlite:database.sql');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //$db->exec("DELETE FROM users");

    //zapisuju do datábaze
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        uid TEXT PRIMARY KEY,
        name TEXT,
        birth_date TEXT,
        hobbies TEXT,
        country TEXT
    )");

foreach ($data as $item) {
    $hobbies = json_encode($item['hobbies']);
    $stmt = $db->prepare("INSERT OR REPLACE INTO users (uid, name, birth_date, hobbies, country) 
    VALUES (:uid, :name, :birth_date, :hobbies, :country)");
    $stmt->bindValue(':uid', $item['uid']);
    $stmt->bindValue(':name', $item['name']);
    $stmt->bindValue(':birth_date', $item['birth_date']);
    $stmt->bindValue(':hobbies', $hobbies);
    $stmt->bindValue(':country', $item['country']);
    $stmt->execute();
}

// Tady ukládám případné změny na databázi
if (isset($_POST['save'])) {
    $uid_old = $_POST['uid_old']; 
    $uid = $_POST['uid'];
    $name = $_POST['name'];
    $birth_date = $_POST['birth_date'];
    $hobbies = json_encode(array_map('trim', explode(',', $_POST['hobbies'])));
    $country = $_POST['country'];
    $stmt = $db->prepare("UPDATE users SET uid = :uid, name = :name, birth_date = :birth_date, hobbies = :hobbies, country = :country WHERE uid = :uid_old");
    $stmt->bindValue(':uid', $uid);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':birth_date', $birth_date);
    $stmt->bindValue(':hobbies', $hobbies);
    $stmt->bindValue(':country', $country);
    $stmt->bindValue(':uid_old', $uid_old);
    $stmt->execute();
    echo "<p style='color:green'>Záznam byl upraven.</p>";
}

// Zobrazení editačního formuláře na potřebné úpravy
if (isset($_GET['edit'])) {
    $uid = $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM users WHERE uid = :uid");
    $stmt->bindValue(':uid', $uid);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $hobbies = json_decode($row['hobbies'], true);
        if (is_array($hobbies)) {
            $hobbies = implode(', ', $hobbies);
        }
        echo "<h2>Editace záznamu</h2>";
        echo "<form method='post'>
                <input type='hidden' name='uid_old' value='" . htmlspecialchars($row['uid']) . "'>
                UID: <input type='text' name='uid' value='" . htmlspecialchars($row['uid']) . "' required><br>
                Jméno: <input type='text' name='name' value='" . htmlspecialchars($row['name']) . "' required><br>
                Datum narození: <input type='text' name='birth_date' value='" . htmlspecialchars($row['birth_date']) . "'><br>
                Zájmy (čárkami oddělené): <input type='text' name='hobbies' value='" . htmlspecialchars($hobbies) . "'><br>
                Země: <input type='text' name='country' value='" . htmlspecialchars($row['country']) . "'><br>
                <input type='submit' name='save' value='Uložit'>
                <a href='" . $_SERVER['PHP_SELF'] . "'>Zpět</a>
              </form>";
    }
}


// Tu vypisuju tabulku na stránce
$sql = "SELECT * FROM users";
$stmt = $db->query($sql);

echo "<table border='1'>";
echo "<tr>
        <th class='bgcolor' >UID</th>
        <th class='bgcolor'>Name</th>
        <th class='bgcolor'>Birth date</th>
        <th class='bgcolor'>Hobbies</th>
        <th class='bgcolor'>Country</th>
      </tr>";
      foreach ($stmt as $row) {
    
    $hobbies = json_decode($row['hobbies'], true);
    if (is_array($hobbies)) {
        $hobbies = implode(', ', $hobbies);
    } else {
        $hobbies = htmlspecialchars($row['hobbies']);
    }

    echo "<tr>";
    echo "<td>" . ($row['uid']) . "</td>";
    echo "<td>" . ($row['name']) . "</td>";
    echo "<td>" . ($row['birth_date']) . "</td>";
    echo "<td>" . $hobbies . "</td>";
    echo "<td>" . ($row['country']) . "</td>";
    echo "<td><a href='?edit=" . urlencode($row['uid']) . "'>Edit</a></td>"; 
    echo "</tr>";

}
echo "</table>";
?>