<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID du lieu est fourni dans l'URL
if (!isset($_GET['id_lieu'])) {
    $_SESSION['error'] = "ID du lieu manquant.";
    header("Location: manage-places.php");
    exit();
}

$id_lieu = filter_input(INPUT_GET, 'id_lieu', FILTER_VALIDATE_INT);

// Vérifiez si l'ID du lieu est un entier valide
if (!$id_lieu && $id_lieu !== 0) {
    $_SESSION['error'] = "ID du lieu invalide.";
    header("Location: manage-places.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nouveauLieu = filter_input(INPUT_POST, 'nouveauLieu', FILTER_VALIDATE_INT);

    try {
        // Mettre à jour le nom du lieu
        $queryUpdateLieu = "UPDATE epreuve SET id_lieu = :nouveauLieu WHERE id_epreuve = :idEpreuve";
        
        $statementUpdateLieu = $connexion->prepare($queryUpdateLieu);
        $statementUpdateLieu->bindParam(":nouveauLieu", $nouveauLieu, PDO::PARAM_INT);
        $statementUpdateLieu->bindParam(":idLieu", $id_lieu, PDO::PARAM_INT);
        $statementUpdateLieu->execute();

        $_SESSION['success'] = "Le lieu a été modifié avec succès.";
        header("Location: manage-places.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-places.php?id_lieu=$id_lieu");
        exit();
    }
}

// Récupérez les informations du lieu pour affichage dans le formulaire
try {
    $queryLieu = "SELECT id_lieu, nom_lieu FROM LIEU WHERE id_lieu = :idLieu";
    $statementLieu = $connexion->prepare($queryLieu);
    $statementLieu->bindParam(":idLieu", $id_lieu, PDO::PARAM_INT);
    $statementLieu->execute();

    if ($statementLieu->rowCount() > 0) {
        $lieu = $statementLieu->fetch(PDO::FETCH_ASSOC);
        $nomLieuActuel = $lieu['nom_lieu'];
    } else {
        $_SESSION['error'] = "Lieu non trouvé.";
        header("Location: manage-places.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-places.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/normalize.css">
    <link rel="stylesheet" href="../../../css/styles-computer.css">
    <link rel="stylesheet" href="../../../css/styles-responsive.css">
    <link rel="shortcut icon" href="../../../img/favicon-jo-2024.ico" type="image/x-icon">
    <title>Modifier un Lieu - Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
    </style>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="../admin-places/manage-places.php">Gestion Lieux</a></li>
                <li><a href="../admin-events/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-contries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-gender/manage-gender.php">Gestion Genres</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Modifier un Lieu</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="modify-places.php?id_lieu=<?php echo $id_lieu; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce lieu?')">
            <label for="currentLieu">Lieu actuel :</label>
            <input type="text" id="currentLieu" value="<?php echo htmlspecialchars($nomLieuActuel); ?>" disabled>
            <label for="currentLieu">Nouveau lieu :</label>
            <select name="currentLieu" id="nouveauLieu" required>
                <?php
                // Récupérer tous les lieux disponibles
                $queryAllLieux = "SELECT id_lieu, nom_lieu FROM LIEU";
                $statementAllLieux = $connexion->prepare($queryAllLieux);
                $statementAllLieux->execute();

                // Afficher les options de la liste déroulante
                while ($lieu = $statementAllLieux->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($lieu['id_lieu'] == $lieuId) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($lieu['id_lieu']) . "' $selected>" . htmlspecialchars($lieu['nom_lieu']) . "</option>";
                }
                ?>
            </select>
            <input type="submit" value="Modifier le Lieu">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-places.php">Retour à la gestion des Lieux</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>
