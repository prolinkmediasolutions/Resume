# Prolink corporate website update

Download **prolink-polished-update.zip** from this folder using GitHub's **Download raw file** button. This is the update for the existing Laravel corporate site, not the resume website at the repository root.

Back up the existing website files and database first. Extract the ZIP on your computer. Read **READ-ME-FIRST.md** before uploading.

- Merge the contents of `prolink-app/` into your hosting account's existing `prolink-app` folder.
- Merge the contents of `public_html/` into the existing `public_html` folder.
- Do not delete existing folders or replace `.env`, `vendor`, `storage`, `index.php`, or the database.
- Follow the included cache-clearing and optional project-seeding commands. These commands may require cPanel Terminal or hosting support.

The update contains no production passwords, customer database, signatures, or session files. Your live hosting has not been changed. Test on staging before applying it to the live site.
