import sqlite3

# Using the path confirmed by your inspection script
DB_PATH = './backend/gym_app.db'

def add_weight_column():
    try:
        conn = sqlite3.connect(DB_PATH)
        cursor = conn.cursor()
        
        print(f"Connecting to: {DB_PATH}")
        
        # Adding the weight column as a REAL (decimal) type
        cursor.execute("ALTER TABLE Sets ADD COLUMN weight REAL DEFAULT 0.0")
        
        conn.commit()
        print("SUCCESS: 'weight' column added to the 'Sets' table.")
        
    except sqlite3.OperationalError as e:
        # This catches errors like 'duplicate column name' if you already ran this
        if "duplicate column name" in str(e).lower():
            print("INFO: The 'weight' column already exists in your table.")
        else:
            print(f"DATABASE ERROR: {e}")
            
    finally:
        if 'conn' in locals():
            conn.close()
            print("Database connection closed.")

if __name__ == "__main__":
    add_weight_column()