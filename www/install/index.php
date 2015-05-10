<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Instalace</title>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/foundation/5.5.2/css/foundation.min.css"></link>
</head>
<body>

	<div class="row">
		<div class="small-12">

<?php
	if (isset($_POST['install'])) {
		if (file_exists("../../app/config/config.local.neon")) {
			printForm();return;
		}

		$dbName = $_POST['dbName'];
		$dbServer = $_POST['dbServer'];
		$dbLogin = $_POST['dbLogin'];
		$dbPass = $_POST['dbPass'];
		$name = $_POST['adminName'];
		$surname = $_POST['adminSurname'];

		try {
			$dbh = new PDO("mysql:host=$dbServer;dbname=$dbName", $dbLogin, $dbPass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			$dbh->prepare(file_get_contents("./create.sql"))->execute();
			$sql = "INSERT INTO `users` (`id`, `name`, `surname`, `login`, `password`, `role`, `room`, `last_activity`)"
				. " VALUES (1, '$name', '$surname', 'admin', '$2y$10$750n59Q4wALlSK1D1mc29.ZuM03nzJ49gBbD9ditHu5Z3YK9zq4OS', 'admin', NULL, NULL);";

			$dbh->exec($sql);

			$config = "parameters:\n";
			$config .= "database:\n";
			$config .= "\tdsn: 'mysql:host=$dbServer;dbname=$dbName'\n";
			$config .= "\tuser: $dbLogin\n";
			$config .= "\tpassword: $dbPass\n";
			$config .= "\toptions:\n";
			$config .= "\t\tlazy:yes\n\n\n";

			$config .= "doctrine:\n";
			$config .= "\thost: $dbServer\n";
			$config .= "\tuser: $dbLogin\n";
			$config .= "\tpassword: $dbPass\n";
			$config .= "\tdbname: $dbName\n";
			$config .= "\tmetadata:\n";
			$config .= "\t\tApp: %appDir%\n";

			file_put_contents('../../app/config/config.local.neon', $config);

			echo "<div class='alert-box success'>Gratulujeme! Právě jste nainstalovali portál WAPPS! Zároveň byl vytvořen administrátorský účet s těmito přihlašovacími údaji: <br/>
					<strong>Uživatelské jméno:</strong> admin<br/>
					<strong>Heslo:</strong> 123<br/>
					Toto heslo si po přihlášení do aplikace urychleně změňte!
					</div>";

		} catch (Exception $e) {
			echo "<div class='alert-box alert'>Vyskytla se chyba! Text chyby: " . $e->getMessage() . "</div>";
			printForm();
			return;
		}
	} else {
			printForm();
	}
?>

		</div>
	</div>

</body>
</html>

<style>
	body {
		padding-top:50px;
		background: #f5f5f5;
	}
</style>


<?php
function printForm() {
	?>

	<?php
	if (file_exists("../../app/config/config.local.neon")) {


		?>
		<div class="panel">Instalace již byla provedena. Pokud chcete znovu nastavit přístupy k databázi, smažte
			soubor config.local.neon ve složce app/config
		</div>

	<?php
	} else {
		?>

		<div class="panel">
			<h1>Vítejte</h1>
			<p>U instalace webového portálu pro podporu výuky (WAPPS). Níže zadejte potřebné údaje.</p>
		</div>

		<form action="" method="post">
			<label> Přihlašovací jméno databázového uživatele
				<input type="text" name="dbLogin"/>
			</label>
			<label> Heslo databázového uživatele
				<input type="password" name="dbPass"/>
			</label>
			<label> Název databáze
				<input type="text" name="dbName"/>
			</label>

			<label> Databázový server
				<input type="text" name="dbServer" value="127.0.0.1"/>
			</label>

			<label> Jméno administrátora aplikace
				<input type="text" name="adminName"/>
			</label>

			<label> Příjmení administrátora aplikace
				<input type="text" name="adminSurname"/>
			</label>

			<input type="submit" name="install" value="Instalovat" class="button"/>
		</form>

	<?php }

}
?>