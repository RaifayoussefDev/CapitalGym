<?php
session_start();
require "../inc/conn_db.php";

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    header('Content-Type: application/json');

    $session_planning_id = isset($_GET['session_IdSp']) ? intval($_GET['session_IdSp']) : null;
    $user_id = isset($_GET['user_ID']) ? intval($_GET['user_ID']) : null;

    if ($user_id === null) {
        if (isset($_SESSION['id'])) {
            $user_id = intval($_SESSION['id']);
        } else {
            echo json_encode(["status" => "error", "message" => "User session not found or invalid."]);
            exit;
        }
    }

    if ($session_planning_id === null) {
        echo json_encode(["status" => "error", "message" => "Session planning ID is missing or invalid."]);
        exit;
    }

    try {
        $check_reservation_query = "SELECT id, etat_reservation FROM reservations WHERE user_id = ? AND session_planning_id = ?";
        $stmt_check = $conn->prepare($check_reservation_query);
        $stmt_check->bind_param("ii", $user_id, $session_planning_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $stmt_check->bind_result($reservation_id, $etat_reservation);
            $stmt_check->fetch();

            if ($etat_reservation === 'annuler') {
                $update_reservation_query = "UPDATE reservations SET etat_reservation = 'Confirmer' WHERE id = ?";
                $stmt_update = $conn->prepare($update_reservation_query);

                if (!$stmt_update) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Erreur de préparation de la requête : " . $conn->error
                    ]);
                    exit;
                }

                $stmt_update->bind_param("i", $reservation_id);

                // Exécuter la mise à jour
                if ($stmt_update->execute()) {
                    if ($stmt_update->affected_rows > 0) {
                        $conn->commit();
                        echo json_encode(["status" => "success", "message" => "Réservation de la séance bien effectuée."]);
                    } else {
                        $conn->rollback();
                        echo json_encode(["status" => "error", "message" => "Aucune modification apportée à la réservation."]);
                    }
                } else {
                    $conn->rollback();
                    echo json_encode([
                        "status" => "error",
                        "message" => "Erreur lors de la mise à jour : " . $stmt_update->error
                    ]);
                }
            } elseif ($etat_reservation === 'confirmer') {
                echo json_encode(["status" => "error", "message" => "Vous avez déjà réservé cette session."]);
            }
        } else {
            $conn->begin_transaction();

            $user_check_query = "SELECT id FROM users WHERE id = ?";
            $stmt_user = $conn->prepare($user_check_query);
            $stmt_user->bind_param("i", $user_id);
            $stmt_user->execute();
            $stmt_user->store_result();

            if ($stmt_user->num_rows === 0) {
                echo json_encode(["status" => "error", "message" => "User does not exist."]);
                exit;
            }

            $stmt_reservation = $conn->prepare("INSERT INTO reservations (user_id, session_planning_id, date_reservation, etat_reservation) VALUES (?, ?, NOW(), 'Confirmer')");
            $stmt_reservation->bind_param("ii", $user_id, $session_planning_id);
            $stmt_reservation->execute();

            $stmt_update = $conn->prepare("UPDATE session_planning SET remaining_slots = remaining_slots - 1 WHERE id = ? AND remaining_slots > 0");
            $stmt_update->bind_param("i", $session_planning_id);
            $stmt_update->execute();

            if ($stmt_update->affected_rows > 0) {
                $conn->commit();
                echo json_encode(["status" => "success", "message" => "Réservation de la séance bien effectuée."]);
            } else {
                $conn->rollback();
                echo json_encode(["status" => "error", "message" => "Aucune place disponible ou ID de séance invalide22."]);
            }
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    } finally {
        $stmt_check->close();
        $conn->close();
    }
}
