<?php

function renderAuthorsList($pdo) {
    $stmt = $pdo->query("SELECT * FROM Forfattare");
    $authors = $stmt->fetchAll();

    foreach ($authors as $author) {
        echo htmlspecialchars($author['namn']) . " — ";
        echo htmlspecialchars((string) $author['fodelsear']) . "<br>";
    }
}
function renderBooksList($pdo) {
    $sql = "
        SELECT Bok.bok_id, Bok.titel, Bok.utgivningsar, Forfattare.namn,
        CASE
            WHEN EXISTS (
                SELECT 1
                FROM lan
                WHERE lan.bok_id = Bok.bok_id
                  AND lan.aterlamningsdatum IS NULL
            )
            THEN 'Utlånad'
            ELSE 'Tillgänglig'
        END AS status
        FROM Bok
        JOIN Forfattare
            ON Bok.forfattar_id = Forfattare.forfattar_id
        ORDER BY Bok.utgivningsar ASC
    ";

    $stmt = $pdo->query($sql);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1'>";
    echo "<tr>
            <th>Titel</th>
            <th>Utgivningsår</th>
            <th>Författare</th>
            <th>Visa</th>
            <th>Status</th>
          </tr>";

    foreach ($books as $book) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($book['titel']) . "</td>";
        echo "<td>" . htmlspecialchars((string)$book['utgivningsar']) . "</td>";
        echo "<td>" . htmlspecialchars($book['namn']) . "</td>";

        echo "<td>
                <a href='book.php?bok_id=" . (int)$book['bok_id'] . "'>
                    <button type='button'>Visa</button>
                </a>
              </td>";

        if ($book['status'] === 'Utlånad') {
            echo "<td style='color: red;'>🔴 Utlånad</td>";
        } else {
            echo "<td style='color: green;'>🟢 Tillgänglig</td>";
        }

        echo "</tr>";
    }

    echo "</table>";
}

function returnBook($bok_id, $pdo) {
    $sql = "
        UPDATE lan
        SET aterlamningsdatum = CURDATE()
        WHERE bok_id = :bok_id
          AND aterlamningsdatum IS NULL
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':bok_id' => $bok_id
    ]);

    return $stmt->rowCount() > 0;
}







function addAuthorAndBook($namn, $fodelsear, $titel, $utgivningsar, $pdo) {
    $sql = "INSERT INTO Forfattare (namn, fodelsear)
            VALUES (:namn, :fodelsear)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':namn' => $namn,
        ':fodelsear' => $fodelsear
    ]);

    $forfattar_id = $pdo->lastInsertId();

    $sql = "INSERT INTO Bok (titel, utgivningsar, forfattar_id)
            VALUES (:titel, :utgivningsar, :forfattar_id)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':titel' => $titel,
        ':utgivningsar' => $utgivningsar,
        ':forfattar_id' => $forfattar_id
    ]);
}

function addBorrower($namn, $medlem_since, $bok_id, $pdo) {
    $sql = "INSERT INTO lantagare (namn, medlem_since)
            VALUES (:namn, :medlem_since)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':namn' => $namn,
        ':medlem_since' => $medlem_since
    ]);
}


?>