import sqlite3
import os

def update_exercise_to_body():
    # 1. Define the filename
    db_name = 'gym_app.db'
    
    # 2. Get the absolute path
    current_dir = os.path.dirname(os.path.abspath(__file__))
    db_path = os.path.join(current_dir, db_name)
    
    print(f"Checking for database at: {db_path}")

    if not os.path.exists(db_path):
        print("CRITICAL ERROR: Database file not found.")
        return

    try:
        conn = sqlite3.connect(db_path)
        cursor = conn.cursor()

        # Updated to set type to 'body'
        sql_update = """
            UPDATE Activities 
            SET set_type = 'body' 
            WHERE activity_name LIKE '%push%up%' 
               OR activity_name LIKE '%pull%up%';
        """

        cursor.execute(sql_update)
        rows_affected = cursor.rowcount
        conn.commit()
        
        print(f"Connection successful!")
        print(f"Rows updated to 'body': {rows_affected}")

        # Verification: Show us everything now set to 'body'
        cursor.execute("SELECT activity_name, set_type FROM Activities WHERE set_type = 'body'")
        results = cursor.fetchall()
        
        if not results:
            print("No rows currently match 'body'.")
        else:
            print("\nCurrent Activities set to 'body':")
            for row in results:
                print(f"- {row[0]}: {row[1]}")

    except sqlite3.Error as e:
        print(f"SQL Error: {e}")
        
    finally:
        if 'conn' in locals():
            conn.close()

if __name__ == "__main__":
    update_exercise_to_body()