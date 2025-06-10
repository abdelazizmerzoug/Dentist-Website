<?php
// Database connection
include "./db.php";

// Initialize the where clause
$whereClause = "a.confirmation = 0"; // Only fetch unconfirmed appointments

// Filter Logic
if (!empty($_GET['filter_name'])) {
    $filterName = $conn->real_escape_string($_GET['filter_name']);
    $whereClause .= " AND p.first_name LIKE '%$filterName%'";
}

if (!empty($_GET['filter_phone'])) {
    $filterPhone = $conn->real_escape_string($_GET['filter_phone']);
    $whereClause .= " AND p.phone LIKE '%$filterPhone%'";
}

if (!empty($_GET['filter_doctor'])) {
    $filterDoctorId = intval($_GET['filter_doctor']);
    $whereClause .= " AND a.id_doctor = $filterDoctorId";
}

if (!empty($_GET['filter_date'])) {
    $filterDate = $conn->real_escape_string($_GET['filter_date']);
    $whereClause .= " AND a.appointment_date = '$filterDate'";
}

// Fetch data
$sql = "SELECT 
            a.id_appointment AS id, 
            p.id_patient AS patient_id,
            p.first_name AS patient_first_name, 
            p.last_name AS patient_last_name, 
            p.birth_date, 
            p.phone, 
            p.email, 
            a.appointment_date, 
            a.appointment_time, 
            a.reason, 
            d.id_doctor,
            d.first_name AS doctor_first_name, 
            d.last_name AS doctor_last_name 
        FROM appointments a
        JOIN patients p ON a.id_patient = p.id_patient
        JOIN doctors d ON a.id_doctor = d.id_doctor
        WHERE $whereClause
        ORDER BY a.appointment_date, a.appointment_time";
$result = $conn->query($sql);

// Fetch all doctors for the dropdown
$doctorsQuery = "SELECT id_doctor, CONCAT(first_name, ' ', last_name) AS doctor_name FROM doctors";
$doctorsResult = $conn->query($doctorsQuery);

// Helper function
function calculateAge($birthDate)
{
    $dob = new DateTime($birthDate);
    $today = new DateTime();
    return $today->diff($dob)->y;
}

// Handle confirmation or deletion actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm'])) {
        $appointmentId = intval($_POST['id']);
        $conn->query("UPDATE appointments SET confirmation = 1 WHERE id_appointment = $appointmentId");
    }

    if (isset($_POST['delete'])) {
        $appointmentId = intval($_POST['id']);

        // Get the patient ID for the appointment
        $stmt = $conn->prepare("SELECT id_patient FROM appointments WHERE id_appointment = ?");
        $stmt->bind_param("i", $appointmentId);
        $stmt->execute();
        $stmt->bind_result($patientId);
        $stmt->fetch();
        $stmt->close();

        if ($patientId) {
            // Delete the appointment
            $stmt = $conn->prepare("DELETE FROM appointments WHERE id_appointment = ?");
            $stmt->bind_param("i", $appointmentId);
            $stmt->execute();
            $stmt->close();

            // Check if the patient has other appointments
            $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE id_patient = ?");
            $stmt->bind_param("i", $patientId);
            $stmt->execute();
            $stmt->bind_result($appointmentCount);
            $stmt->fetch();
            $stmt->close();

            // If no other appointments exist, delete the patient
            if ($appointmentCount === 0) {
                $stmt = $conn->prepare("DELETE FROM patients WHERE id_patient = ?");
                $stmt->bind_param("i", $patientId);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Redirect to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?" . http_build_query($_GET));
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pending Appointments</title>
    <style>
             @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
:root {
    --base-clr: #2e6f85;
    --line-clr: #42434a;
    --hover-clr: #222533;
    --text-clr: #e6e6ef;
    --accent-clr: #5e63ff;
    --secondary-text-clr: #b0b3c1;
}

*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html{
    font-family: Poppins, 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.5rem;
}

body{
    margin-right: 8px;
    min-height: 100vh;
    background-color:#ededed;
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 1em;
}

#sidebar{
    height: 100vh;
    width: 265px;
    padding: 10px 1em;
    background-color: var(--base-clr);
    border-right: 1px solid var(--line-clr);

    position: sticky;
    top: 0;
    align-self: start;
    transition: 300ms ease-in-out;
    overflow: hidden;
    text-wrap: nowrap;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);

}

#sidebar.close{
    padding: 10px;
    width: 60px;
}

#sidebar ul{
    list-style: none;
}

#sidebar > ul > li:first-child{
    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;
    .logo{
        font-weight: 600;
    }
}

#sidebar a, #sidebar .dropdown-btn, #sidebar .logo{
    border-radius: .5em;
    padding: .85em;
    text-decoration: none;
    color: var(--text-clr);
    display: flex;
    align-items: center;
    gap: 1em;
}

.dropdown-btn{
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    font: inherit;
    cursor: pointer;
}

#sidebar svg{
    flex-shrink: 0;
    fill: #ededed;
}

#sidebar a span, #sidebar .dropdown-btn span{
    flex-grow: 1;
}

#sidebar a:hover, #sidebar .dropdown-btn:hover{
    background-color: var(--hover-clr);
}

#sidebar .sub-menu{
display: grid;
grid-template-rows: 0fr;
transition: 300ms ease-in-out;

> div{
    overflow: hidden;
}
}

#sidebar .sub-menu.show{
    grid-template-rows: 1fr;
}

.dropdown-btn svg{
    transition: 200ms ease;
}

.rotate svg:last-child{
    rotate: 180deg;
}

#sidebar .sub-menu a{
    padding-left: 2em;
}

#toggle-btn{
    margin-left: auto;
    border: none;
    border-radius: .5em;
    background: none;
    cursor: pointer;

    svg{
        transition: rotate 150ms ease;
    }
}

#toggle-btn:hover{
    background-color: var(--hover-clr);
}


.container {
            margin-top: 2em;
            width: 100%;
            max-height: 90vh;
            background-color: #ffffff;
            color: #1a344a;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 20px;
            overflow-y: auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.8rem;
            color: #1a344a;
        }

        form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            justify-content: space-between;

        }

        form input, form select, form button {
            padding: 10px;
            border: 1px solid #9ccce2;
            border-radius: 8px;
            font-size: 1rem;
            background: #f0f8ff;
        }

        form input, form select {
            flex: 1;
            background-color: #f0f8ff;
        }

        form button {
            background-color: #287b8e;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        form button:hover {
            background-color: #1a344a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            color: #1a344a;
        }

        th, td {
            text-align: center;
            border: 1px solid #ddd;
            padding: 10px;
        }

        th {
            background-color: #1b4b62;
            color: white;
        }

        tr:hover {
            background-color: #e9ecef;
        }

        td form {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        td button {
            width: 100%;
            padding: 6px; /* Reduced padding for smaller buttons */
            border: none;
            border-radius: 6px;
            cursor: pointer;
            color: #ffffff;
            font-weight: bold;
        }

        td button:first-child {
            background-color: #28a745;
        }

        td button:first-child:hover {
            background-color: #218838;
        }

        td button:last-child {
            background-color: #dc3545;
        }

        td button:last-child:hover {
            background-color: #c82333;
        }
    </style>
    <script>
    function deleteAppointment(appointmentId) {
            if (confirm("Are you sure you want to delete this appointment? This action cannot be undone.")) {
                // Submit the form to delete the appointment
                document.getElementById(`delete-form-${appointmentId}`).submit();
            }
        }
    </script>
</head>
<body>
<nav id="sidebar">
    <ul>
      <li>
        <img src="../images/header-logo.png" style="width: 50px; height: 50px; margin-left: 10px ;" alt="">
        <span class="logo">Smile Haven</span>
        <button onclick=toggleSidebar() id="toggle-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m313-480 155 156q11 11 11.5 27.5T468-268q-11 11-28 11t-28-11L228-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T468-692q11 11 11 28t-11 28L313-480Zm264 0 155 156q11 11 11.5 27.5T732-268q-11 11-28 11t-28-11L492-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T732-692q11 11 11 28t-11 28L577-480Z"/></svg>
        </button>
      </li>
      <li>
        <a href="index.html">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M240-200h120v-200q0-17 11.5-28.5T400-440h160q17 0 28.5 11.5T600-400v200h120v-360L480-740 240-560v360Zm-80 0v-360q0-19 8.5-36t23.5-28l240-180q21-16 48-16t48 16l240 180q15 11 23.5 28t8.5 36v360q0 33-23.5 56.5T720-120H560q-17 0-28.5-11.5T520-160v-200h-80v200q0 17-11.5 28.5T400-120H240q-33 0-56.5-23.5T160-200Zm320-270Z"/></svg>
          <span>Home</span>
        </a>
      </li>
      <li>
        <a href="dashboard.html">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M520-640v-160q0-17 11.5-28.5T560-840h240q17 0 28.5 11.5T840-800v160q0 17-11.5 28.5T800-600H560q-17 0-28.5-11.5T520-640ZM120-480v-320q0-17 11.5-28.5T160-840h240q17 0 28.5 11.5T440-800v320q0 17-11.5 28.5T400-440H160q-17 0-28.5-11.5T120-480Zm400 320v-320q0-17 11.5-28.5T560-520h240q17 0 28.5 11.5T840-480v320q0 17-11.5 28.5T800-120H560q-17 0-28.5-11.5T520-160Zm-400 0v-160q0-17 11.5-28.5T160-360h240q17 0 28.5 11.5T440-320v160q0 17-11.5 28.5T400-120H160q-17 0-28.5-11.5T120-160Zm80-360h160v-240H200v240Zm400 320h160v-240H600v240Zm0-480h160v-80H600v80ZM200-200h160v-80H200v80Zm160-320Zm240-160Zm0 240ZM360-280Z"/></svg>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <button onclick=toggleSubMenu(this) class="dropdown-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M620-163 450-333l56-56 114 114 226-226 56 56-282 282Zm220-397h-80v-200h-80v120H280v-120h-80v560h240v80H200q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h167q11-35 43-57.5t70-22.5q40 0 71.5 22.5T594-840h166q33 0 56.5 23.5T840-760v200ZM480-760q17 0 28.5-11.5T520-800q0-17-11.5-28.5T480-840q-17 0-28.5 11.5T440-800q0 17 11.5 28.5T480-760Z"/></svg>
          <span>Gestion de Stock</span>
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
        </button>
        <ul class="sub-menu">
          <div>
            <li><a href="../html-Client/add_product.php">Ajout un produit</a></li>
            <li><a href="../php-Client/Show_Product.php">Affiches les Produit</a></li>
            <li><a href="../php-Client/pursh_pic.php">Facture</a></li>
          </div>
        </ul>
      </li>
      <li>
        <button onclick=toggleSubMenu(this) class="dropdown-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M680-119q-8 0-16-2t-15-7l-120-70q-14-8-21.5-21.5T500-249v-141q0-16 7.5-29.5T529-441l120-70q7-5 15-7t16-2q8 0 15.5 2.5T710-511l120 70q14 8 22 21.5t8 29.5v141q0 16-8 29.5T830-198l-120 70q-7 4-14.5 6.5T680-119ZM400-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM80-160v-112q0-33 17-62t47-44q51-26 115-44t141-18h14q6 0 12 2-8 18-13.5 37.5T404-360h-4q-71 0-127.5 18T180-306q-9 5-14.5 14t-5.5 20v32h252q6 21 16 41.5t22 38.5H80Zm320-400q33 0 56.5-23.5T480-640q0-33-23.5-56.5T400-720q-33 0-56.5 23.5T320-640q0 33 23.5 56.5T400-560Zm0-80Zm12 400Zm174-166 94 55 94-55-94-54-94 54Zm124 208 90-52v-110l-90 53v109Zm-150-52 90 53v-109l-90-53v109Z"/></svg>          <span>Fournisseurs</span>
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
        </button>
        <ul class="sub-menu">
          <div>
            <li><a href="../html-Client/Supplier.html">Ajout un Fournisseur</a></li>
            <li><a href="Show_Fournisseur.php">Afficher les fournisseur</a></li>
          </div>
        </ul>
      </li>
      <li>
        <a href="Show_Appoitments.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M600-80v-80h160v-400H200v160h-80v-320q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H600ZM320 0l-56-56 103-104H40v-80h327L264-344l56-56 200 200L320 0ZM200-640h560v-80H200v80Zm0 0v-80 80Z"/></svg>
          <span>Appoitments</span>
        </a>
      </li>
      <li>
        <a href="payments.php">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M560-440q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35ZM280-320q-33 0-56.5-23.5T200-400v-320q0-33 23.5-56.5T280-800h560q33 0 56.5 23.5T920-720v320q0 33-23.5 56.5T840-320H280Zm80-80h400q0-33 23.5-56.5T840-480v-160q-33 0-56.5-23.5T760-720H360q0 33-23.5 56.5T280-640v160q33 0 56.5 23.5T360-400Zm440 240H120q-33 0-56.5-23.5T40-240v-440h80v440h680v80ZM280-400v-320 320Z"/></svg>
        <span>Payment Management</span>
        </a>
      </li>
      <li>
        <a href="historique.php" class="active">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M80-200v-80h400v80H80Zm0-200v-80h200v80H80Zm0-200v-80h200v80H80Zm744 400L670-354q-24 17-52.5 25.5T560-320q-83 0-141.5-58.5T360-520q0-83 58.5-141.5T560-720q83 0 141.5 58.5T760-520q0 29-8.5 57.5T726-410l154 154-56 56ZM560-400q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Z"/></svg>
        <span>Historique</span>
        </a>
      </li>
      <li>
        <a href="../php-Client/dynamic-full-calendar.php" class="active">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-40q0-17 11.5-28.5T280-880q17 0 28.5 11.5T320-840v40h320v-40q0-17 11.5-28.5T680-880q17 0 28.5 11.5T720-840v40h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm0-480h560v-80H200v80Zm0 0v-80 80Zm280 240q-17 0-28.5-11.5T440-440q0-17 11.5-28.5T480-480q17 0 28.5 11.5T520-440q0 17-11.5 28.5T480-400Zm-160 0q-17 0-28.5-11.5T280-440q0-17 11.5-28.5T320-480q17 0 28.5 11.5T360-440q0 17-11.5 28.5T320-400Zm320 0q-17 0-28.5-11.5T600-440q0-17 11.5-28.5T640-480q17 0 28.5 11.5T680-440q0 17-11.5 28.5T640-400ZM480-240q-17 0-28.5-11.5T440-280q0-17 11.5-28.5T480-320q17 0 28.5 11.5T520-280q0 17-11.5 28.5T480-240Zm-160 0q-17 0-28.5-11.5T280-280q0-17 11.5-28.5T320-320q17 0 28.5 11.5T360-280q0 17-11.5 28.5T320-240Zm320 0q-17 0-28.5-11.5T600-280q0-17 11.5-28.5T640-320q17 0 28.5 11.5T680-280q0 17-11.5 28.5T640-240Z"/></svg>
          <span>Calendar</span>
        </a>
      </li>
      <li >
        <a href="profile.html">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-240v-32q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v32q0 33-23.5 56.5T720-160H240q-33 0-56.5-23.5T160-240Zm80 0h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
          <span>Profile</span>
        </a>
      </li>
    </ul>
  </nav>

    <div class="container">
        <h1>Pending Appointments</h1>
        <form method="GET">
            <input type="text" name="filter_name" placeholder="Patient Name" value="<?= htmlspecialchars($_GET['filter_name'] ?? '') ?>">
            <input type="text" name="filter_phone" placeholder="Phone" value="<?= htmlspecialchars($_GET['filter_phone'] ?? '') ?>">
            <select name="filter_doctor">
                <option value="">--Select Doctor--</option>
                <?php while ($doctor = $doctorsResult->fetch_assoc()): ?>
                    <option value="<?= $doctor['id_doctor'] ?>" <?= ($_GET['filter_doctor'] ?? '') == $doctor['id_doctor'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($doctor['doctor_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="date" name="filter_date" value="<?= htmlspecialchars($_GET['filter_date'] ?? '') ?>">
            <button type="submit">Filter</button>
            <button type="button" onclick="window.location.href='<?= $_SERVER['PHP_SELF'] ?>'">Reset</button>
        </form>
        <table>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Age</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Date</th>
                <th>Time</th>
                <th>Reason</th>
                <th>Doctor</th>
                <th>Actions</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['patient_first_name'] . ' ' . $row['patient_last_name']) ?></td>
                        <td><?= calculateAge($row['birth_date']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= $row['appointment_date'] ?></td>
                        <td><?= $row['appointment_time'] ?></td>
                        <td><?= htmlspecialchars($row['reason']) ?></td>
                        <td><?= htmlspecialchars($row['doctor_first_name'] . ' ' . $row['doctor_last_name']) ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" name="confirm">Confirm</button>
                                <button type="submit" name="delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10">No pending appointments found</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
    <script>
        const toggleButton = document.getElementById('toggle-btn')
const sidebar = document.getElementById('sidebar')

function toggleSidebar(){
  sidebar.classList.toggle('close')
  toggleButton.classList.toggle('rotate')

  closeAllSubMenus()
}

function toggleSubMenu(button){

  if(!button.nextElementSibling.classList.contains('show')){
    closeAllSubMenus()
  }

  button.nextElementSibling.classList.toggle('show')
  button.classList.toggle('rotate')

  if(sidebar.classList.contains('close')){
    sidebar.classList.toggle('close')
    toggleButton.classList.toggle('rotate')
  }
}

function closeAllSubMenus(){
  Array.from(sidebar.getElementsByClassName('show')).forEach(ul => {
    ul.classList.remove('show')
    ul.previousElementSibling.classList.remove('rotate')
  })
}
    </script>
</body>
</html>
