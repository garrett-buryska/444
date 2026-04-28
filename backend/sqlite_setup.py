import os
import sqlite3

DB_PATH = os.path.join(os.path.dirname(__file__), "gym_app.db")
WORKOUT_CATEGORIES = ("upper", "lower", "arms", "chest", "back", "cardio", "core", "legs")

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

WORKOUT_INSTANCES = [
    {
        "type": "chest",
        "timestamp": "2026-04-01 17:30:00",
        "lifts": [
            ("Bench Press", [(135, 10, 1), (155, 8, 1), (175, 6, 1), (175, 5, 1)], 1),
            ("Incline Dumbbell Press", [(50, 10, 1), (55, 8, 1), (55, 8, 1)], 1),
            ("Cable Fly", [(30, 15, 1), (35, 12, 1), (35, 12, 1)], 1),
            ("Push Up", [(0, 20, 1), (0, 18, 1), (0, 15, 1)], 1),
        ],
    },
    {
        "type": "back",
        "timestamp": "2026-04-03 18:15:00",
        "lifts": [
            ("Pull Up", [(0, 8, 1), (0, 7, 1), (0, 6, 1), (0, 5, 1)], 1),
            ("Barbell Row", [(115, 10, 1), (135, 8, 1), (145, 6, 1), (145, 6, 1)], 1),
            ("Lat Pulldown", [(120, 12, 1), (130, 10, 1), (140, 8, 1)], 1),
            ("Back Extension", [(0, 15, 1), (10, 12, 1), (10, 12, 1)], 1),
        ],
    },
    {
        "type": "legs",
        "timestamp": "2026-04-05 10:00:00",
        "lifts": [
            ("Back Squat", [(185, 8, 1), (205, 6, 1), (225, 5, 1), (225, 5, 1)], 1),
            ("Romanian Deadlift", [(135, 10, 1), (155, 8, 1), (165, 8, 1)], 1),
            ("Leg Press", [(270, 12, 1), (320, 10, 1), (360, 10, 1), (360, 8, 1)], 1),
            ("Standing Calf Raise", [(90, 15, 1), (100, 15, 1), (100, 12, 1), (100, 12, 1)], 1),
        ],
    },
    {
        "type": "upper",
        "timestamp": "2026-04-07 17:45:00",
        "lifts": [
            ("Overhead Press", [(85, 8, 1), (95, 6, 1), (95, 6, 1), (100, 5, 1)], 1),
            ("Seated Cable Row", [(110, 12, 1), (120, 10, 1), (130, 10, 1)], 1),
            ("Dumbbell Shoulder Press", [(40, 10, 1), (45, 8, 1), (45, 8, 1)], 1),
            ("Face Pull", [(45, 15, 1), (50, 15, 1), (50, 12, 1)], 1),
        ],
    },
    {
        "type": "arms",
        "timestamp": "2026-04-09 19:00:00",
        "lifts": [
            ("Dumbbell Curl", [(25, 12, 1), (30, 10, 1), (30, 9, 1)], 1),
            ("Triceps Pushdown", [(60, 15, 1), (70, 12, 1), (75, 10, 1)], 1),
            ("Hammer Curl", [(25, 12, 1), (30, 10, 1), (30, 10, 1)], 1),
            ("Skull Crusher", [(50, 10, 1), (55, 8, 1), (55, 8, 1)], 1),
        ],
    },
    {
        "type": "core",
        "timestamp": "2026-04-11 09:30:00",
        "lifts": [
            ("Plank", [(0, 60, 1), (0, 60, 1), (0, 45, 1)], 1),
            ("Hanging Knee Raise", [(0, 15, 1), (0, 12, 1), (0, 12, 1)], 1),
            ("Russian Twist", [(15, 20, 1), (15, 20, 1), (20, 16, 1)], 1),
            ("Cable Crunch", [(70, 15, 1), (80, 12, 1), (80, 12, 1)], 1),
        ],
    },
    {
        "type": "cardio",
        "timestamp": "2026-04-12 08:15:00",
        "lifts": [
            ("Treadmill Run", [(0, 25, 1)], 1),
            ("Rowing Machine", [(0, 15, 1)], 1),
            ("Jump Rope", [(0, 60, 1), (0, 60, 1), (0, 60, 1), (0, 60, 0), (0, 60, 0)], 0),
        ],
    },
    {
        "type": "lower",
        "timestamp": "2026-04-14 18:00:00",
        "lifts": [
            ("Deadlift", [(225, 5, 1), (245, 4, 1), (265, 3, 1)], 1),
            ("Hip Thrust", [(185, 12, 1), (205, 10, 1), (225, 8, 1), (225, 8, 1)], 1),
            ("Goblet Squat", [(55, 12, 1), (60, 10, 1), (60, 10, 1)], 1),
            ("Step Up", [(25, 10, 1), (30, 10, 1), (30, 8, 1)], 1),
        ],
    },
]

def ensure_activities_secondary_constraint(conn, cursor):
    cursor.execute("""
        SELECT sql
        FROM sqlite_master
        WHERE type = 'table' AND name = 'Activities'
    """)
    table_sql = cursor.fetchone()[0]
    if "secondary_muscle_group TEXT CHECK" in table_sql:
        return

    conn.commit()
    cursor.execute("PRAGMA foreign_keys = OFF;")
    cursor.execute("""
        CREATE TABLE Activities_new (
            activity_name TEXT PRIMARY KEY,
            main_muscle_group TEXT,
            secondary_muscle_group TEXT CHECK (
                secondary_muscle_group IS NULL
                OR secondary_muscle_group IN (
                    'upper',
                    'lower',
                    'arms',
                    'chest',
                    'back',
                    'cardio',
                    'core',
                    'legs'
                )
            ),
            description TEXT,
            youtube_link TEXT,
            set_text TEXT
        )
    """)
    cursor.execute("""
        INSERT INTO Activities_new (
            activity_name,
            main_muscle_group,
            secondary_muscle_group,
            description,
            youtube_link,
            set_text
        )
        SELECT
            activity_name,
            main_muscle_group,
            CASE
                WHEN secondary_muscle_group IN (
                    'upper',
                    'lower',
                    'arms',
                    'chest',
                    'back',
                    'cardio',
                    'core',
                    'legs'
                )
                THEN secondary_muscle_group
                ELSE NULL
            END,
            description,
            youtube_link,
            set_text
        FROM Activities
    """)
    cursor.execute("DROP TABLE Activities")
    cursor.execute("ALTER TABLE Activities_new RENAME TO Activities")
    conn.commit()
    cursor.execute("PRAGMA foreign_keys = ON;")

def init_db():
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    invalid_secondary_categories = sorted({
        secondary
        for _, _, secondary, _, _, _ in ACTIVITIES
        if secondary is not None and secondary not in WORKOUT_CATEGORIES
    })
    if invalid_secondary_categories:
        raise ValueError(
            "Secondary muscle groups must be NULL or one of "
            f"{WORKOUT_CATEGORIES}. Invalid values: {invalid_secondary_categories}"
        )

    cursor.execute("PRAGMA foreign_keys = ON;")

    cursor.execute("""
        CREATE TABLE IF NOT EXISTS User (
            username TEXT PRIMARY KEY,
            password TEXT NOT NULL,
            name TEXT,
            img_url TEXT,
            weight REAL,
            height REAL,
            skill_level TEXT,
            DoB TEXT,
            bench_max REAL,
            squat_max REAL,
            deadlift_max REAL
        )
    """)

    cursor.execute("""
        CREATE TABLE IF NOT EXISTS Activities (
            activity_name TEXT PRIMARY KEY,
            main_muscle_group TEXT,
            secondary_muscle_group TEXT CHECK (
                secondary_muscle_group IS NULL
                OR secondary_muscle_group IN (
                    'upper',
                    'lower',
                    'arms',
                    'chest',
                    'back',
                    'cardio',
                    'core',
                    'legs'
                )
            ),
            description TEXT,
            youtube_link TEXT,
            set_text TEXT
        )
    """)

    cursor.execute("""
        CREATE TABLE IF NOT EXISTS Workout (
            workout_Id INTEGER PRIMARY KEY AUTOINCREMENT,
            workout_type TEXT,
            time_stamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            username TEXT,
            FOREIGN KEY (username) REFERENCES User(username)
        )
    """)

    cursor.execute("""
        CREATE TABLE IF NOT EXISTS Lift (
            liftID INTEGER PRIMARY KEY AUTOINCREMENT,
            workoutID INTEGER,
            activity_name TEXT,
            num_sets INTEGER,
            completed BOOLEAN,
            FOREIGN KEY (workoutID) REFERENCES Workout(workout_Id),
            FOREIGN KEY (activity_name) REFERENCES Activities(activity_name)
        )
    """)

    cursor.execute("""
        CREATE TABLE IF NOT EXISTS Sets (
            liftID INTEGER,
            set_number INTEGER,
            weight REAL,
            reps INTEGER,
            completed BOOLEAN,
            PRIMARY KEY (liftID, set_number),
            FOREIGN KEY (liftID) REFERENCES Lift(liftID)
        )
    """)

    ensure_activities_secondary_constraint(conn, cursor)

    cursor.execute("""
        INSERT OR IGNORE INTO User (username, password, name, skill_level, bench_max, squat_max, deadlift_max)
        VALUES (?, ?, ?, ?, ?, ?, ?) 
    """, ("gym_bro_99", "ironlifter1", "Chad Smith", "Advanced", 225, 315, 405))

    cursor.execute("""
        INSERT OR IGNORE INTO User (username, password, name, weight, height, skill_level, bench_max, squat_max, deadlift_max)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) 
    """, ("demo_lifter", "demo123", "Demo Lifter", 185, 70, "Intermediate", 205, 275, 335))

    cursor.executemany("""
        INSERT OR REPLACE INTO Activities (
            activity_name,
            main_muscle_group,
            secondary_muscle_group,
            description,
            youtube_link,
            set_text
        )
        VALUES (?, ?, ?, ?, ?, ?)
    """, ACTIVITIES)

    seed_username = "gym_bro_99"

    cursor.execute("""
        DELETE FROM Sets
        WHERE liftID IN (
            SELECT Lift.liftID
            FROM Lift
            JOIN Workout ON Workout.workout_Id = Lift.workoutID
            WHERE Workout.username = ?
        )
    """, (seed_username,))
    cursor.execute("""
        DELETE FROM Lift
        WHERE workoutID IN (
            SELECT workout_Id
            FROM Workout
            WHERE username = ?
        )
    """, (seed_username,))
    cursor.execute("DELETE FROM Workout WHERE username = ?", (seed_username,))

    for workout in WORKOUT_INSTANCES:
        cursor.execute("""
            INSERT INTO Workout (workout_type, time_stamp, username)
            VALUES (?, ?, ?)
        """, (workout["type"], workout["timestamp"], seed_username))
        workout_id = cursor.lastrowid

        for activity_name, sets, lift_completed in workout["lifts"]:
            cursor.execute("""
                INSERT INTO Lift (workoutID, activity_name, num_sets, completed)
                VALUES (?, ?, ?, ?)
            """, (workout_id, activity_name, len(sets), lift_completed))
            lift_id = cursor.lastrowid

            cursor.executemany("""
                INSERT INTO Sets (liftID, set_number, weight, reps, completed)
                VALUES (?, ?, ?, ?, ?)
            """, [
                (lift_id, set_number, weight, reps, completed)
                for set_number, (weight, reps, completed) in enumerate(sets, start=1)
            ])

    conn.commit()
    print(f"Gym Database initialized with seed workouts at {DB_PATH}.\n")

    cursor.execute("SELECT name FROM sqlite_master WHERE type='table';")
    tables = cursor.fetchall()
    print("Created Tables:", [t[0] for t in tables])
    cursor.execute("SELECT COUNT(*) FROM Activities;")
    print("Activities:", cursor.fetchone()[0])
    cursor.execute("SELECT COUNT(*) FROM Workout WHERE username = ?;", (seed_username,))
    print(f"Seed workouts for {seed_username}:", cursor.fetchone()[0])

    conn.close()

if __name__ == "__main__":
    init_db()
