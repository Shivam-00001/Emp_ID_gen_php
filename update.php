<?php
include('db.php');


if (isset($_GET['id'])) {
    $employeeId = $_GET['id'];


    $sql = "SELECT * FROM employees WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employeeId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
    } else {
        echo "Employee not found.";
        exit();
    }
}


if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $dob = $_POST['dob'];
    $imagePath = $employee['image_path']; 

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";


        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $imageName = uniqid() . "_" . basename($_FILES['image']['name']);
        $imagePath = $targetDir . $imageName;

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (in_array($_FILES['image']['type'], $allowedTypes)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                echo "<div class='alert alert-success mt-3'>File uploaded successfully.</div>";
            } else {
                echo "<div class='alert alert-danger mt-3'>Error uploading the file. Please try again.</div>";
            }
        } else {
            echo "<div class='alert alert-danger mt-3'>Invalid file type. Please upload an image (JPEG, PNG, GIF).</div>";
        }
    }

    // Update employee details in the database using prepared statements
    $sql = "UPDATE employees SET name = ?, email = ?, mobile = ?, dob = ?, image_path = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $name, $email, $mobile, $dob, $imagePath, $employeeId);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success mt-3'>Employee details updated successfully.</div>";
        header("Location: index.php"); 
        exit();
    } else {
        echo "<div class='alert alert-danger mt-3'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Update Employee Details</h2>

    <!-- Form to update employee details -->
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($employee['name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="mobile" class="form-label">Mobile Number</label>
            <input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($employee['mobile']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="dob" class="form-label">Date of Birth</label>
            <input type="date" class="form-control" id="dob" name="dob" value="<?php echo htmlspecialchars($employee['dob']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Upload New Image (Optional)</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*">
            <?php if (!empty($employee['image_path'])): ?>
                <p>Current Image: <img src="<?php echo $employee['image_path']; ?>" alt="Employee Image" style="max-width: 100px; max-height: 100px;"></p>
            <?php endif; ?>
        </div>
        <button type="submit" name="update" class="btn btn-primary w-100">Update</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
