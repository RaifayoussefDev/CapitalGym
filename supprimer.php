<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion des Utilisateurs</title>
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
</head>

<body>
    <h2>Liste des Utilisateurs</h2>
    <table id="userTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>CIN</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $servername = "localhost";
            $username = "root";
            $password = ""; // Replace with your password
            $dbname = "privilage";
            // Create a database connection
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            // Fetch users
            $sql = "SELECT id, cin, nom, prenom, email FROM users";
            $result = $conn->query($sql);

            // Display each user in a table row
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['cin'] ?></td>
                    <td><?= $row['nom'] ?></td>
                    <td><?= $row['prenom'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td>
                        <form action="delete_user.php" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');">
                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                            <button type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile;
            $conn->close();
            ?>
        </tbody>
    </table>

    <!-- Initialize DataTables -->
    <script>
        $(document).ready(function() {
            $('#userTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' // Load French translations
                },
                paging: true,
                searching: true,
                ordering: true,
            });
        });
    </script>
</body>

</html>