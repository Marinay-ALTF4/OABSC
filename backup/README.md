# Disaster Recovery Backups

This folder contains one-click scripts for database and file backup/restore.

## Files

- `backup-disaster-recovery.bat`
- `restore-disaster-recovery.bat`
- `scripts/backup-disaster-recovery.ps1`
- `scripts/restore-disaster-recovery.ps1`

## What is backed up

- MySQL database from the current `.env` settings.
- `uploads/profiles`
- `writable/uploads`

The scripts save output to `writable/backups`, which should stay out of git.

## One-click Backup

### Detailed backup steps

#### Option 1: Double-click backup

1. Open the project folder `C:\xampp\htdocs\OABSC`.
2. Open the `backup` folder.
3. Find `backup-disaster-recovery.bat`.
4. Double-click `backup-disaster-recovery.bat`.
5. If Windows asks whether you want to run the file, click `Run anyway` or `More info` then `Run anyway`.
6. A black PowerShell/Command window will open.
7. Wait until the window shows `Backup completed successfully.`
8. Do not close the window early.
9. Open `C:\xampp\htdocs\OABSC\writable\backups`.
10. Confirm that these files were created:
	- `latest.sql`
	- `latest.zip`
	- timestamped backup files such as `oabsc-db-YYYYMMDD-HHMMSS.sql`
	- timestamped archive files such as `oabsc-dr-YYYYMMDD-HHMMSS.zip`

#### Option 2: Run from PowerShell

1. Open PowerShell.
2. Go to your project folder:

```powershell
cd C:\xampp\htdocs\OABSC
```

3. Run the backup script:

```powershell
powershell -ExecutionPolicy Bypass -File .\backup\scripts\backup-disaster-recovery.ps1
```

4. Wait until the script finishes.
5. Look for the message `Backup completed successfully.`
6. Check `writable\backups` for the files listed above.

#### What gets included in the backup

1. The MySQL database using the values from your `.env` file.
2. User profile uploads in `uploads\profiles`.
3. Runtime uploads in `writable\uploads`.

#### What to do after backup

1. Copy the backup files to another drive, USB, or cloud storage if you want extra safety.
2. Keep more than one backup copy if possible.
3. Test the restore process occasionally so you know it works before an emergency.

## One-click Restore

### Detailed restore steps

#### Option 1: Double-click restore

1. Open the project folder `C:\xampp\htdocs\OABSC`.
2. Open the `backup` folder.
3. Find `restore-disaster-recovery.bat`.
4. Double-click `restore-disaster-recovery.bat`.
5. The script will automatically look for the latest backup.
6. Wait until the window shows `Restore completed successfully.`
7. Open the app in your browser and check if the data is back.

#### Option 2: Restore from PowerShell

1. Open PowerShell.
2. Go to your project folder:

```powershell
cd C:\xampp\htdocs\OABSC
```

3. Restore the latest backup automatically:

```powershell
powershell -ExecutionPolicy Bypass -File .\backup\scripts\restore-disaster-recovery.ps1
```

4. Wait for the restore to finish.
5. If the database and files were restored correctly, you should see the success message.

#### Restore from a specific backup file

1. If you want to use a specific archive, replace the backup path below with your file.
2. Run:

```powershell
powershell -ExecutionPolicy Bypass -File .\backup\scripts\restore-disaster-recovery.ps1 -BackupPath .\writable\backups\latest.zip
```

#### Recreate the database first

Use this only if you want the script to drop and recreate the database before restoring:

```powershell
powershell -ExecutionPolicy Bypass -File .\backup\scripts\restore-disaster-recovery.ps1 -DropExisting
```

## Notes

- The scripts look for XAMPP MySQL tools at `C:\xampp\mysql\bin` first.
- If the root MySQL password is blank, the scripts handle it automatically.
- The database restore command uses `mysql.exe`; the backup uses `mysqldump.exe`.
