<?php
$servername = "localhost";
$username = "root";
$password = "Rajesh@122";
$dbname = "task_manager";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message
$message = '';
$titleError = '';

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to validate title (only alphabets and spaces allowed)
function validateTitle($title) {
    return preg_match('/^[a-zA-Z\s]+$/', $title);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_task']) || isset($_POST['update_task'])) {
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $status = sanitizeInput($_POST['status']);

        // Validation
        if (empty($title) || empty($description) || !in_array($status, ['pending', 'completed']) || !validateTitle($title)) {
            if (!validateTitle($title)) {
                $titleError = 'Title should contain only alphabets and spaces.';
            }
            $message = 'Please fill in all fields correctly.';
        } else {
            if (isset($_POST['add_task'])) {
                // Add new task
                $sql = "INSERT INTO tasks (title, description, status) VALUES ('$title', '$description', '$status')";
                if ($conn->query($sql) === TRUE) {
                    header("Location: task_list.php?message=" . urlencode('Task added successfully.')); // Redirect to the next page with a success message
                    exit();
                } else {
                    $message = 'Error adding task: ' . $conn->error;
                }
            } elseif (isset($_POST['update_task'])) {
                // Update existing task
                $id = intval($_POST['id']);
                if ($id <= 0) {
                    $message = 'Invalid task ID.';
                } else {
                    $sql = "UPDATE tasks SET title='$title', description='$description', status='$status' WHERE id=$id";
                    if ($conn->query($sql) === TRUE) {
                        header("Location: task_list.php?message=" . urlencode('Task updated successfully.')); // Redirect to the next page with a success message
                        exit();
                    } else {
                        $message = 'Error updating task: ' . $conn->error;
                    }
                }
            }
        }
    } elseif (isset($_POST['delete_task'])) {
        $id = intval($_POST['id']);
        if ($id <= 0) {
            $message = 'Invalid task ID.';
        } else {
            $sql = "DELETE FROM tasks WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                header("Location: task_list.php?message=" . urlencode('Task deleted successfully.')); // Redirect to the next page with a success message
                exit();
            } else {
                $message = 'Error deleting task: ' . $conn->error;
            }
        }
    }
}

// Fetch tasks for displaying in the form
$editTask = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    if ($id > 0) {
        $sql = "SELECT * FROM tasks WHERE id=$id";
        $result = $conn->query($sql);
        $editTask = $result->fetch_assoc();
    }
}

$sql = "SELECT * FROM tasks";
$result = $conn->query($sql);
$tasks = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Task Management</h1>
    </header>

    <!-- Add/Edit Task Form -->
    <div id="task-form">
        <h2><?php echo isset($editTask) ? 'Edit Task' : 'Add New Task'; ?></h2>
        <form method="POST" action="index.php">
            <input type="hidden" name="id" value="<?php echo isset($editTask['id']) ? intval($editTask['id']) : ''; ?>">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?php echo isset($editTask['title']) ? htmlspecialchars($editTask['title']) : ''; ?>" required>
            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?php echo isset($editTask['description']) ? htmlspecialchars($editTask['description']) : ''; ?></textarea>
            <label for="status">Status:</label>
            <select id="status" name="status">
                <option value="pending" <?php echo isset($editTask['status']) && $editTask['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="completed" <?php echo isset($editTask['status']) && $editTask['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
            </select>
            <button type="submit" name="<?php echo isset($editTask) ? 'update_task' : 'add_task'; ?>">
                <?php echo isset($editTask) ? 'Update Task' : 'Add Task'; ?>
            </button>
            <!-- Display title error below the button -->
            <?php if ($titleError): ?>
                <div class="error" style="color: red;"><?php echo $titleError; ?></div>
            <?php endif; ?>
            <?php if ($message && !$titleError): ?>
                <div class="message" style="color: red;"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
