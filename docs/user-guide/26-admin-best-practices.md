# Administrator Guide: Best Practices and Checklists

These checklists combine the features described in the other chapters into practical routines. Follow them in order.

## Publishing a course end to end

1. **Prepare the basics.** Make sure the right **Category** exists (Catalog > Categories) and, if you want a teacher shown, the **Instructor** (Catalog > Instructors).
2. **Create the course as Draft.** Catalog > Courses > Add course. Fill in title, description, level, duration and a banner image (JPEG, PNG, GIF or WebP, up to 2 MB). Leave Status on Draft.
3. **Decide the audience.** Set "Who can access this course": everyone, Agents only, all Partners, or one Partner organization.
4. **Decide the lesson rule.** Keep "Students must complete lessons in order" on for guided courses, or turn it off to let learners jump around.
5. **Build the curriculum.** Open the **Curriculum** tab. Add sections, then lessons in the order learners should follow them. Choose the right content type (Text, YouTube, PDF or Video) and respect the file limits (PDF 50 MB, video 100 MB). The order you add things is the order learners see.
6. **Add assessments.** Create quizzes under Assessments > Quizzes, place them on the course, a section or a lesson, set the passing grade and attempt rules, and add questions. Keep quizzes Published only when they are ready.
7. **Add translations.** Fill in the Kinyarwanda sections (see below).
8. **Preview as a learner.** Use **View site** to check how the course looks. Staff are not locked out of lessons, so to test the lesson order and quiz gates use a test learner account.
9. **Publish.** On the courses list press **Publish**, or set Status to Published in the course form. Confirm the message "Course published."
10. **Optionally add it to a bundle** (Catalog > Bundles) and schedule **live sessions**.

## Onboarding a partner organization

1. Go to Learners > Partner organizations > **Add organization**, enter the name and keep it Active.
2. Note the generated **Registration code** in the list.
3. Share the code privately with the organization's coordinator. Learners choose the organization and enter the code when they register.
4. If the organization needs its own training, create or edit a course and set access to "Specific Partner organization", then choose the organization. Use "All Partners" for content shared by every partner.
5. Optionally add the organization's logo under Site > Partner logos so it appears on the home page. This is separate from the organization record.
6. Later, check that the right people joined: Learners > Users, and filter or search for the organization's learners.
7. If a code is shared too widely, use **New code** to regenerate it. The old code stops working for new sign-ups; existing learners are not affected.
8. To stop new sign-ups without losing history, untick Active. Do not delete an organization that has learners or courses; the system will not allow it.

## Running a cohort

1. Create the course (or reuse one) and publish it.
2. Get learners in: either they register and enroll themselves, or you import them (see the next section).
3. Schedule **live sessions** for the course and invite enrolled students. They receive an email with the join link; only invited students see it.
4. Monitor with **Student progress**: open the course to see each student's status (Not started, In progress, Completed) and search by name or email.
5. Watch the **Dashboard**, selecting the course in the filter. "Courses Requiring Attention" points out low completion, low quiz scores or lack of activity.
6. Review **Quiz results** for the cohort's quizzes and use **Export CSV** to share results.
7. Follow up with learners who have not started. Use the user's View page to see progress across all courses.
8. At the end, export quiz results, and consider unpublishing or archiving what is no longer offered (Draft for courses, Archived for bundles).

## Importing students

1. Export the progress CSV from the old platform. Check that it has **First Name, Last Name, Email and % Completed** columns and is under 5 MB.
2. Make sure the target course already exists, is complete, and has its lessons in the right order, because progress is applied to lessons in order.
3. Check the course audience. Learners who are outside it will be skipped.
4. Import students > pick the course > choose the file > **Preview**.
5. Read the preview carefully: counts of Create, Enroll existing, Already enrolled and Skip, and the notes for each row. Fix any rows marked Skip (invalid) in the CSV and preview again.
6. Press **Import now** and read the summary and any error lines.
7. Tell new learners that no email was sent and that they should use "Forgot password" to set a password.
8. Repeat for each other course, one CSV per course. Re-running the same file is safe; completed lessons are not duplicated.

## Keeping content translated

- Fill the Kinyarwanda section whenever you create or edit a category, course, section, lesson, quiz, question or answer option.
- Missing translations fall back to English field by field, so you can translate gradually. Courses with a translation show "Kinyarwanda translation available" in the list.
- When you change English text, update its Kinyarwanda version too, otherwise learners see the old translation.
- Files (PDF, video) are not translated. Provide separate lessons or files if needed.
- Have a Kinyarwanda speaker review before publishing.

## Before going live

Go through this list for each course and for the whole platform.

- [ ] The course has a title, description, banner, category and (if wanted) an instructor.
- [ ] Every section has lessons, and every lesson opens correctly (text shown, YouTube link plays, PDF and video files load).
- [ ] The lesson order switch is set as intended.
- [ ] The access level and partner organization are right.
- [ ] Quizzes have questions, one correct answer each, a sensible passing grade, time limit and attempt rules, and are Published.
- [ ] Kinyarwanda translations are filled in where needed.
- [ ] Live sessions have the correct date, time, duration and a working https meeting link.
- [ ] The platform logo (Settings) and partner logos are set.
- [ ] Staff accounts have only the permissions they need, former staff are deactivated, and there are at least two active Super Admins.
- [ ] Partner organization registration codes have been shared only with the right people.
- [ ] You know who is answering the Contact messages inbox.
- [ ] The course Status is **Published**.

## Good habits

- Use Draft while you work and publish only when ready. Unpublishing a course hides it from the public site but keeps your content and learners' progress.
- Prefer **Deactivate** over **Delete** for users and organizations, since deletion cannot be undone.
- Review the permission setup whenever someone joins, changes role or leaves.
- Check the Contact messages inbox regularly and delete read messages to keep it tidy.
- Use the Dashboard's period filter to compare cohorts, and the Reports page for per-course completion and enrollment history.
