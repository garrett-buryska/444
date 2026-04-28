import os
import sqlite3

# Path to your database file
DB_PATH = os.path.join(os.path.dirname(__file__), "gym_app.db")

# Your list of activities
ACTIVITIES = [
    ("Bench Press", "chest", "arms", "Lay on a flat bench and press the bar from chest level to locked arms.", "https://www.youtube.com/shorts/0cXAp6WhSj4", "4 sets x 6-8 reps"),
    ("Incline Dumbbell Press", "chest", "upper", "Press dumbbells up on an incline bench while keeping shoulder blades tight.", "https://www.tiktok.com/@davis.diley/video/7434588122056363307", "3 sets x 8-10 reps"),
    ("Cable Fly", "chest", "upper", "Bring cable handles together in a wide arc while keeping a slight elbow bend.", "https://www.youtube.com/shorts/3nNSsW9IigQ", "3 sets x 12-15 reps"),
    ("Push Up", "chest", "arms", "Lower your chest toward the floor and push back up with a straight body line.", "https://www.youtube.com/shorts/ba8tr1NzwXU", "3 sets x 12-20 reps"),
    ("Pull Up", "back", "arms", "Hang from a bar and pull your chin over it while controlling the descent.", "https://www.youtube.com/shorts/OEXosPwzFdc", "4 sets x 5-8 reps"),
    ("Lat Pulldown", "back", "arms", "Pull the bar to your upper chest while keeping your torso controlled.", "https://www.youtube.com/shorts/hnSqbBk15tw", "3 sets x 8-12 reps"),
    ("Barbell Row", "back", "lower", "Hinge forward and row the bar toward your lower ribs.", "https://www.youtube.com/shorts/Nqh7q3zDCoQ", "4 sets x 6-10 reps"),
    ("Seated Cable Row", "back", "arms", "Pull the cable handle toward your torso and squeeze your shoulder blades.", "https://www.youtube.com/shorts/qD1WZ5pSuvk", "3 sets x 10-12 reps"),
    ("Back Extension", "back", "lower", "Hinge at the hips and raise your torso until your body is straight.", "https://www.youtube.com/shorts/P489_62b8JU", "3 sets x 12-15 reps"),
    ("Back Squat", "legs", "core", "Squat with a bar on your upper back, keeping your chest tall and knees tracking.", "https://www.youtube.com/shorts/PPmvh7gBTi0", "4 sets x 5-8 reps"),
    ("Front Squat", "legs", "core", "Squat with the bar racked across the front of your shoulders.", "https://www.youtube.com/shorts/r6Z_h_WAX5o", "3 sets x 5-8 reps"),
    ("Romanian Deadlift", "legs", "lower", "Hinge at the hips with a slight knee bend and lower the bar along your legs.", "https://www.youtube.com/shorts/5rIqP63yWFg", "3 sets x 8-10 reps"),
    ("Leg Press", "legs", "lower", "Press the sled away while keeping your lower back against the pad.", "https://www.youtube.com/shorts/nDh_BlnLCGc", "4 sets x 10-12 reps"),
    ("Walking Lunge", "legs", "lower", "Step forward into a lunge and alternate legs with controlled strides.", "https://www.tiktok.com/@dannibelle_/video/7476633692710948112", "3 sets x 10 reps each leg"),
    ("Leg Curl", "legs", "lower", "Curl the pad toward your body while keeping hips down.", "https://www.youtube.com/shorts/xdbEG3xGLI8", "3 sets x 10-15 reps"),
    ("Standing Calf Raise", "legs", None, "Rise onto the balls of your feet, pause, and lower under control.", "https://www.youtube.com/shorts/rsOLKY02m70", "4 sets x 12-15 reps"),
    ("Deadlift", "lower", "back", "Lift the bar from the floor by driving through your legs and locking out tall.", "https://www.youtube.com/shorts/ZaTM37cfiDs", "3 sets x 3-5 reps"),
    ("Hip Thrust", "lower", "legs", "Drive your hips upward with your upper back supported on a bench.", "https://www.youtube.com/shorts/GAZC6bt30Yg", "4 sets x 8-12 reps"),
    ("Goblet Squat", "lower", "core", "Hold a dumbbell at chest level and squat with an upright torso.", "https://www.youtube.com/shorts/p6_qyFEOvC0", "3 sets x 10-12 reps"),
    ("Step Up", "lower", "legs", "Step onto a box or bench and stand tall before lowering with control.", "https://www.youtube.com/shorts/lUgdjxy_8WI", "3 sets x 10 reps each leg"),
    ("Overhead Press", "upper", "arms", "Press the bar from shoulder height to overhead while bracing your core.", "https://www.youtube.com/shorts/MKX_h0SHDG0", "4 sets x 5-8 reps"),
    ("Dumbbell Shoulder Press", "upper", "arms", "Press dumbbells overhead while seated or standing.", "https://www.youtube.com/shorts/OLePvpxQEGk", "3 sets x 8-10 reps"),
    ("Lateral Raise", "upper", None, "Raise dumbbells out to the sides until shoulder height.", "https://www.tiktok.com/@jeremyethier/video/7511388093153283345", "3 sets x 12-15 reps"),
    ("Face Pull", "upper", "back", "Pull the rope toward your face with elbows high and shoulder blades back.", "https://www.facebook.com/jerseydemic/videos/-rear-delt-face-pull-quick-tips-for-perfect-form/1871209330496782/", "3 sets x 12-15 reps"),
    ("Dumbbell Curl", "arms", None, "Curl dumbbells up while keeping elbows close to your sides.", "https://www.youtube.com/shorts/PuaJzTatIJM", "3 sets x 10-12 reps"),
    ("Hammer Curl", "arms", None, "Curl dumbbells with palms facing each other.", "https://www.youtube.com/shorts/NyW2fT2gQhM", "3 sets x 10-12 reps"),
    ("Triceps Pushdown", "arms", None, "Push the cable attachment down until your elbows are straight.", "https://www.instagram.com/reel/DChNea9g6Pq/?hl=en", "3 sets x 10-15 reps"),
    ("Skull Crusher", "arms", None, "Lower the bar toward your forehead and extend your elbows back up.", "https://www.instagram.com/reel/DR5wkvTCgOi/", "3 sets x 8-10 reps"),
    ("Dip", "arms", "chest", "Lower your body between parallel bars and press back to the top.", "https://www.youtube.com/shorts/CrbIq-T-h8I", "3 sets x 6-10 reps"),
    ("Plank", "core", "upper", "Hold a straight body position on elbows or hands while bracing your abs.", "https://www.youtube.com/shorts/hoeNgjheDHk", "3 sets x 45-60 sec"),
    ("Hanging Knee Raise", "core", None, "Hang from a bar and raise your knees toward your chest.", "https://www.youtube.com/shorts/qazbsy9eU-M", "3 sets x 10-15 reps"),
    ("Russian Twist", "core", None, "Rotate side to side while seated with your torso leaned back.", "https://www.youtube.com/shorts/KUsvxlmpPoI", "3 sets x 20 total reps"),
    ("Cable Crunch", "core", None, "Kneel at a cable station and crunch down by flexing your abs.", "https://www.instagram.com/reel/DNWta-EsAHM/", "3 sets x 12-15 reps"),
    ("Mountain Climber", "core", "cardio", "Drive your knees toward your chest from a plank position.", "https://www.youtube.com/shorts/hZb6jTbCLeE", "3 sets x 30-45 sec"),
    ("Treadmill Run", "cardio", "legs", "Run at a steady pace on the treadmill.", "https://www.tiktok.com/@trainingtall/video/7454644817969286446", "1 set x 20-30 min"),
    ("Stationary Bike", "cardio", "legs", "Pedal at moderate intensity while keeping a steady cadence.", "https://www.youtube.com/shorts/sM6X37l95B8", "1 set x 20-30 min"),
    ("Rowing Machine", "cardio", "back", "Drive with your legs, lean back, and pull the handle to your ribs.", "https://www.youtube.com/shorts/978LzxkqJ0M", "1 set x 15-20 min"),
    ("Jump Rope", "cardio", "legs", "Jump rhythmically while turning the rope with your wrists.", "https://www.youtube.com/shorts/pcOkhja6wug", "5 rounds x 1 min"),
    ("Sled Push", "cardio", "legs", "Drive the sled forward with short powerful steps.", "https://www.youtube.com/shorts/di9H5xkKmi4", "6 rounds x 20 yards"),
]

def reset_and_seed_database():
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()

    # 1. Disable foreign keys so we can clear tables in any order
    cursor.execute("PRAGMA foreign_keys = OFF;")

    # 2. Clear all tables
    tables = ["Sets", "Lift", "Workout", "User", "Activities"]
    for table in tables:
        # Check if table exists before deleting to avoid errors
        cursor.execute(f"SELECT name FROM sqlite_master WHERE type='table' AND name='{table}'")
        if cursor.fetchone():
            cursor.execute(f"DELETE FROM {table}")

    # 3. Re-seed Activities
    # Ensure table exists first
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS Activities (
            activity_name TEXT PRIMARY KEY,
            main_muscle_group TEXT,
            secondary_muscle_group TEXT CHECK (
                secondary_muscle_group IS NULL
                OR secondary_muscle_group IN (
                    'upper', 'lower', 'arms', 'chest', 'back', 'cardio', 'core', 'legs'
                )
            ),
            description TEXT,
            youtube_link TEXT,
            set_text TEXT
        )
    """)

    cursor.executemany("""
        INSERT INTO Activities (
            activity_name,
            main_muscle_group,
            secondary_muscle_group,
            description,
            youtube_link,
            set_text
        )
        VALUES (?, ?, ?, ?, ?, ?)
    """, ACTIVITIES)

    # 4. Re-enable foreign keys
    cursor.execute("PRAGMA foreign_keys = ON;")

    conn.commit()
    conn.close()
    print("Database cleared and Activities table re-seeded successfully.")

if __name__ == "__main__":
    reset_and_seed_database()