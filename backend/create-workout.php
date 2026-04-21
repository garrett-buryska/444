<?php
// Ensure no whitespace/HTML before this tag to avoid redirect errors

try {
    // 1. Updated Database Connection Path
    // '../' moves up one level out of 'frontend' and into the root, 
    // then 'backend/' enters the backend folder.
    $db = new PDO('sqlite:./gym_app.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Get the Category from your "Customize Workout" form
    $selectedCategory = $_GET['category'] ?? 'chest'; // Default to 'chest' if not provided

    // 3. Create the Workout Header
    $stmt = $db->prepare("INSERT INTO Workout (time_stamp, workout_type) VALUES (CURRENT_TIMESTAMP, ?)");
    $stmt->execute([$selectedCategory]);
    
    // Capture the ID for the redirect
    $workoutId = $db->lastInsertId();

    // 4. Select 4 Random Lifts from the Chosen Category
    $query = "SELECT activity_name FROM Activities WHERE main_muscle_group = ? ORDER BY RANDOM() LIMIT 4";
    $stmt = $db->prepare($query);
    $stmt->execute([$selectedCategory]);
    $lifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Safety check: ensure at least 4 exercises exist in this category
    if (count($lifts) >= 4) {
        
        // 5. Insert Lifts and Sets (2 or 3) into the junction table
        $insertSetStmt = $db->prepare("INSERT INTO Lift (workoutID, activity_name, num_sets) VALUES (?, ?, ?)");

        foreach ($lifts as $lift) {
            // Every lift gets 2 or 3 sets
            $numSets = rand(2, 3);
            $insertSetStmt->execute([$workoutId, $lift['activity_name'], $numSets]);
        }

        // 6. Redirect to current-workout.html with the ID
        header("Location: ../frontend/current-workout.html?id=" . $workoutId);
        exit();

    } else {
        echo "Error: Found only " . count($lifts) . " exercises for '$selectedCategory'. You need at least 4.";
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
?>