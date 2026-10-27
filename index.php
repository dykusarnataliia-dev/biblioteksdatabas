<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/functions.php";

if (isset($_POST['submit-author'])) {
    addAuthorAndBook(
        trim($_POST['namn']),
        isset($_POST['fodelsear']) && $_POST['fodelsear'] !== ''
            ? (int) $_POST['fodelsear']
            : null,
        trim($_POST['titel']),
        (int) $_POST['utgivningsar'],
        $pdo
    );
}

if (isset($_POST['submit-borrower'])) {
    addBorrower(
        trim($_POST['namn']),
        $_POST['medlem_since'],
        (int) $_POST['bok_id'],
        $pdo
    );

    echo "<p class='success'>Du har registrerats framgångsrikt!</p>";
}

// Повернення книги
if (isset($_POST['return-book'], $_POST['return_bok_id'])) {
    $bok_id = (int) $_POST['return_bok_id'];

    if (returnBook($bok_id, $pdo)) {
        echo "<p class='success'>Boken har återlämnats!</p>";
    } else {
        echo "<p>Boken kunde inte återlämnas.</p>";
    }
}
?>


<!doctype html>
<html lang="sv">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style.css">
  <title>Bibliotekets böcker</title>

</head>
<body>


<div id="author-list">
<h1>Bibliotekets bokregister</h1>
			<h2>Böcker</h2>
 <p>Här kan du se bibliotekets böcker med titel, utgivningsår och författarens namn.</p>
<?php renderBooksList($pdo); ?>
	</div>
	

  <h2>Bibliotekets böcker</h2>
<p>Du kan också lägga till nya böcker och författare.</p>

  <form id="authorForm" action="" method="post">
		<label for="namn">Författarens namn</label>
		<input id="namn" name="namn" type="text"  maxlength="80" required  placeholder="Författarens namn">
		<label for="utgivningsar">Utgivningsår</label>
		<input id="utgivningsar" name="utgivningsar"
       type="number" min="1000" max="2100"
       placeholder="Bokens utgivningsår" required>
		<label for="titel">Bokens titel</label>
		<input id="titel" name="titel" type="text" maxlength="80" required placeholder="Bokens titel">
		<div class="actions">
			<input type="submit"  value="Lägg till författare"  name="submit-author">
		</div>
	
    </select>
 
</form>
		
		
		
		
		
  
    <h2>Registrering av låntagare</h2>
   <p>Här kan du registrera nya låntagare och välja vilken bok de vill låna.</p>
  <form id="borrowerForm" action="" method="post">
    <label for="namn">Namn</label>
    <input id="namn" name="namn" type="text" maxlength="80"  required placeholder="Låntagarens namn">
	<label for="medlem_since">Medlem sedan</label>
    <input id="medlem_since" name="medlem_since" type="date" required>

    <label for="bok_id" type="text" maxlength="80">Välj bok</label>

<select id="bok_id" name="bok_id">
    <option value="">-- Välj en bok --</option>

    <?php
    $stmt = $pdo->query("SELECT bok_id, titel FROM Bok ORDER BY titel ASC");
    $books = $stmt->fetchAll();

    foreach ($books as $book) {
        echo '<option value="' . $book['bok_id'] . '">';
        echo htmlspecialchars($book['titel']);
        echo '</option>';
    }
    ?>
</select>
	
	

    <div class="actions">
        <input type="submit" value="Lägg till låntagare" name="submit-borrower">
    </div>
</form>	
	
	<h2>Återlämna bok</h2>

<form method="post" action="">
    <select name="return_bok_id" required>
        <option value="">-- Välj utlånad bok --</option>

        <?php
        $stmt = $pdo->query("
            SELECT DISTINCT Bok.bok_id, Bok.titel
            FROM Bok
            INNER JOIN lan ON Bok.bok_id = lan.bok_id
            WHERE lan.aterlamningsdatum IS NULL
            ORDER BY Bok.titel ASC
        ");

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $book) {
            echo '<option value="' . (int)$book['bok_id'] . '">'
                . htmlspecialchars($book['titel'])
                . '</option>';
        }
        ?>
    </select>

    <button type="submit" name="return-book">
        Återlämna bok
    </button>
</form>
	
	

	
	
	
	
	
	
	
	
	
	


 
 
</body>
</html>