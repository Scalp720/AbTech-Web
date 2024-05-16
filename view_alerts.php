<?php
session_start();
require_once 'db.php'; // Ensure correct path to your db.php file

$selectedRegion = isset($_GET['region']) ? intval($_GET['region']) : null;
$selectedCity = isset($_GET['city']) ? intval($_GET['city']) : null;
$selectedMunicipality = isset($_GET['municipality']) ? intval($_GET['municipality']) : null;
$selectedStatus = isset($_GET['status']) ? $_GET['status'] : null;


// Fetch user data based on session user_id (using prepared statements)
$user = null; // Initialize to null
if (isset($_SESSION['user_id'])) {
    $stmt = $DB->prepare("SELECT * FROM tb_user WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        die("Error fetching user data");
    }
}

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || ($user !== null && $user['user_type'] !== 'admin')) { 
    header("Location: login.php"); // Redirect to login if not logged in or not an admin
    exit();
}

// Build the SQL query
$sql = "SELECT a.* FROM tb_alert a
        JOIN tb_user u ON a.user_id = u.user_id
        JOIN tb_responder r ON u.user_id = r.user_id
        JOIN tb_office o ON r.office_id = o.office_id
        JOIN tb_municipality m ON o.municipal_id = m.municipal_id
        JOIN tb_city c ON m.city_id = c.city_id
        JOIN tb_region rg ON c.region_id = rg.region_id
        WHERE 1=1 "; // Start with a condition that's always true

if ($selectedRegion) {
    $sql .= "AND rg.region_id = ? ";
}
if ($selectedCity) {
    $sql .= "AND c.city_id = ? ";
}
if ($selectedMunicipality) {
    $sql .= "AND m.municipal_id = ? ";
}
if ($selectedStatus) {
    $sql .= "AND a.status = ? ";
}


// Prepare and execute the query
$stmt = $DB->prepare($sql);
$types = "";
$params = [];

if ($selectedRegion) {
    $types .= "i";
    $params[] = &$selectedRegion;
}
if ($selectedCity) {
    $types .= "i";
    $params[] = &$selectedCity;
}
if ($selectedMunicipality) {
    $types .= "i";
    $params[] = &$selectedMunicipality;
}
if ($selectedStatus) {
    $types .= "s"; // 's' for string type (ENUM)
    $params[] = &$selectedStatus;
}

if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch region, city, and municipality data for filter options
$regions = $DB->query("SELECT * FROM tb_region ORDER BY office_name");
$cities = $DB->query("SELECT * FROM tb_city ORDER BY office_name");
$municipalities = $DB->query("SELECT * FROM tb_municipality ORDER BY office_name"); // Order municipalities alphabetically

?>
<!DOCTYPE html>
<?php require_once 'header.php'; ?>

<div class="container">
    <h2>View Alerts</h2>
    <form method="get" action="">
        <label for="region">Region:</label>
        <select name="region" id="region" onchange="this.form.submit()">  
            <option value="">-- Select Region --</option>
            <?php while ($regionRow = $regions->fetch_assoc()): ?>
                <option value="<?php echo $regionRow['region_id']; ?>" <?php if ($selectedRegion == $regionRow['region_id']) echo 'selected'; ?>>
                    <?php echo $regionRow['office_name']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="city">City:</label>
        <select name="city" id="city" onchange="this.form.submit()">
            <option value="">-- Select City --</option>
            <?php while ($cityRow = $cities->fetch_assoc()): ?>
                <?php if (!$selectedRegion || $cityRow['region_id'] == $selectedRegion): ?>
                    <option value="<?php echo $cityRow['city_id']; ?>" <?php if ($selectedCity == $cityRow['city_id']) echo 'selected'; ?>>
                        <?php echo $cityRow['office_name']; ?>
                    </option>
                <?php endif; ?>
            <?php endwhile; ?>
        </select>

        <label for="municipality">Municipality:</label>
        <select name="municipality" id="municipality" onchange="this.form.submit()">
            <option value="">-- Select Municipality --</option>
            <?php while ($municipalityRow = $municipalities->fetch_assoc()): ?>
                <?php if (!$selectedCity || $municipalityRow['city_id'] == $selectedCity): ?>
                    <option value="<?php echo $municipalityRow['municipal_id']; ?>" <?php if ($selectedMunicipality == $municipalityRow['municipal_id']) echo 'selected'; ?>>
                        <?php echo $municipalityRow['office_name']; ?>
                    </option>
                <?php endif; ?>
            <?php endwhile; ?>
        </select>
    </form>
    <?php if ($result && $result->num_rows > 0): ?>
        <h3 class="alert">Alerts</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Location</th>
                <th>Description</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Status</th>
                
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row["alert_id"]; ?></td>
                    <td><?php echo $row["user_id"]; ?></td>
                    <td><?php echo $row["location"]; ?></td>
                    <td><?php echo $row["description"]; ?></td>
                    <td><?php echo $row["latitude"]; ?></td>
                    <td><?php echo $row["longitude"]; ?></td>
                    <td><?php echo $row["created_at"]; ?></td>
                    <td><?php echo $row["updated_at"]; ?></td>
                    <td><?php echo $row["status"]; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No alerts found.</p>
    <?php endif; ?>
</div>
<?php require_once 'footer.php'; ?>
