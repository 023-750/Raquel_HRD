Evaluation Flow Check

Main branch
Department
> Acquired Properties  
> Audit 
> Business Development 
> Compliance 
> Finance
> General Services
> Human resources 
> Information Technology
> Marketing
> Office of the President
> Operations
> Purchasing

Personal Note:
> Test the remaining department (evaluation)
powershell command under (Cloudflared) - [C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe]:
.\cloudflared.exe tunnel --url http://localhost/Raquel_HRD_System/ 



Note for fred:
> first things first, your should delete the current raquel pawnshop system (files who already into your device) because it is outdated.
> Second, drop the database "raquel_hris" on your selected browser http://localhost/phpmyadmin/
> Once you already followed the the first 2 instructions above, you can now proceed to the next steps.
> Navigate to "C:\xampp\htdocs", Once you already at this directory, now open your command prompt under this directory.
> On cmd type:
- git clone -b kiro-version https://github.com/justinelusteriorovira-ai/FINAL_RAQUEL_PAWNSHOP_HRD.git
> It should be look like this during the process:

C:\xampp\htdocs>git clone -b kiro-version https://github.com/justinelusteriorovira-ai/FINAL_RAQUEL_PAWNSHOP_HRD.git
Cloning into 'FINAL_RAQUEL_PAWNSHOP_HRD'...
remote: Enumerating objects: 1570, done.
remote: Counting objects: 100% (30/30), done.
remote: Compressing objects: 100% (27/27), done.
remote: Total 1570 (delta 6), reused 17 (delta 3), pack-reused 1540 (from 1)
Receiving objects: 100% (1570/1570), 44.81 MiB | 9.49 MiB/s, done.
Resolving deltas: 100% (980/980), done.

> Once the cloning process has been completed successfully, you should go to https://localhost/phpmyadmin/ on your selected browser.

> Import all database in order:
1. 1st_schema_tables.sql
2. 2nd_seed_organization.sql
3. 3rd_seed_HR_accounts_.sql
4. AP_seed.sql
5. Audit_seed.sql
6. BD_seed.sql
7. Compliance_seed.sql
8. Finance_seed.sql
9. GS_seed.sql
10. HR_seed.sql
11. IT_seed.sql
12. Marketing_seed.sql
13. OP_seed.sql
14. Operations_seed.sql
15. Purchasing_seed.sql
16. xPortal_accounts.sql
17. zLAST_performance_indexes.sql
18. demo_seed.sql - optional this is for me by the way.

> Now that you already imported all of the neccessarry databses, go to files and navigate to folder named "config" under that folder you should be able to see the database.php, look for the "define('DB_PASS', 'admin');" then change it into "define('DB_PASS', '');" - don't include the quotation u dummy.

> Final step, open your browser and type this url: http://localhost/FINAL_RAQUEL_PAWNSHOP_HRD/ you'll be able to access the system completely without having any errors.