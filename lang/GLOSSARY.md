# Koralink translation guide (English → Kinyarwanda)

All screen text lives in `lang/rw.json` (key = the exact English text, value = Kinyarwanda).
Laravel's own messages (validation errors, login failures, password reset) are in `lang/rw/*.php`.
English is the default and the fallback: any missing translation shows in English.

## Style
- Natural, clear, respectful Kinyarwanda for adult learners. Address the reader as **wowe** (singular "you"): *ushobora…, ugomba…, injira…*
- Short buttons use the imperative: **Bika** (Save), **Siba** (Delete), **Hindura** (Edit).
- Keep placeholders exactly as written (`:name`, `:count`, `:date`…) and keep punctuation and capital letters at the start of sentences.
- Do **not** translate brand and product names: **Koralink**, **KoraLink Agents Academy**, **Mastercard**, **SFH**, **Digital Jobs for Youth in Health** (translate only the surrounding sentence).
- Use the words below every time, so the platform reads consistently.

## Core terms
| English | Kinyarwanda | Notes |
|---|---|---|
| Home | Ahabanza | |
| Dashboard | Ikibaho | |
| Course / Courses | Isomo / Amasomo | a whole course |
| Lesson / Lessons | Icyigwa / Ibyigwa | one lesson inside a course |
| Section / Chapter | Umutwe / Imitwe | a group of lessons |
| Quiz / Quizzes | Isuzuma / Amasuzuma | |
| Question / Questions | Ikibazo / Ibibazo | |
| Answer | Igisubizo | |
| Option / Options | Ihitamo / Amahitamo | |
| Score | Amanota | |
| Passing grade | Amanota yo gutsinda | |
| Passed / Failed | Watsinze / Ntiwatsinze | |
| Attempt | Igerageza | |
| Submit | Ohereza | |
| Enroll / Enrollment | Iyandikishe mu isomo / Kwiyandikisha | |
| Enrolled | Wiyandikishije | |
| Progress | Aho ugeze | "iterambere" for charts/reports |
| Completed | Byarangiye | |
| Learner / Student | Umunyeshuri / Abanyeshuri | |
| Agent / Agents | Agent / Aba-agent | loanword, widely used in Rwanda |
| Partner / Partners | Umufatanyabikorwa / Abafatanyabikorwa | |
| Partner organization | Umuryango mufatanyabikorwa | |
| Instructor | Umwarimu / Abarimu | |
| Bundle / Learning path | Ipaki y'amasomo / Amapaki y'amasomo | |
| Live session | Isomo rya live | |
| Category / Categories | Icyiciro / Ibyiciro | |
| Certificate | Icyemezo | |

## Account and actions
| English | Kinyarwanda |
|---|---|
| Log in / Log out | Injira / Sohoka |
| Register / Sign up | Iyandikishe |
| Account | Konti |
| Profile | Umwirondoro |
| Password | Ijambobanga |
| Forgot your password? | Wibagiwe ijambobanga? |
| Email | Imeri |
| Save | Bika |
| Cancel | Hagarika |
| Delete | Siba |
| Edit | Hindura |
| Add / Create | Ongeraho / Kora |
| Search | Shakisha |
| View | Reba |
| Browse | Reba |
| Back | Subira inyuma |
| Next / Previous | Ibikurikira / Ibyabanje |
| Settings | Igenamiterere |
| Users | Abakoresha |
| Roles & permissions | Inshingano n'uburenganzira |
| Reports | Raporo |
| Contact | Twandikire |
| About | Abo turi bo |
| Language | Ururimi |
| Yes / No | Yego / Oya |
| Optional | Si ngombwa |
| Required | Ngombwa |
| Active / Inactive | Bikora / Ntibikora |

## Course words
| English | Kinyarwanda |
|---|---|
| Published | Byatangajwe |
| Draft | Ntibiratangazwa |
| Free | Ku buntu |
| Price | Igiciro |
| Level | Urwego |
| Beginner / Intermediate / Advanced | Utangira / Hagati / Urwego rwo hejuru |
| Duration | Igihe bimara |
| Self-paced | Wiga ku muvuduko wawe |
| Access code | Kode yo kwinjira |
| Additional details | Andi makuru |
| Phone number | Nimero ya telefone |
| National ID number | Nimero y'indangamuntu |
| Date of birth | Itariki y'amavuko |
| Gender (Male / Female / Other / Prefer not to say) | Igitsina (Gabo / Gore / Ikindi / Sinshaka kubivuga) |

## Review
These translations were drafted for the project and should be reviewed by a native Kinyarwanda speaker
(ideally a professional translator) before launch. Edit `lang/rw.json` directly; no code changes needed.
Run `php artisan test --filter=LocalizationTest` afterwards: it checks that every text has a translation
and that placeholders (`:name`) are kept.

## Working with translations
- **Screen text:** edit `lang/rw.json`. New text in the code is written as `__('English text')`; add the same English text as a key in `rw.json`.
  If a key is missing the page simply shows English, and `php artisan test --filter=TranslationCoverageTest` lists what is missing.
- **Course content** (titles, descriptions, lessons, quiz questions and answers, categories, bundles, instructors, live sessions):
  in the admin forms, open "Kinyarwanda translation (optional)" and fill only what you want. Empty fields show the English original.
  The English text is always the original and is what admins edit.
- **Language choice:** the EN | RW switcher remembers the choice in the browser and, for signed-in users, on their account.
- **Adding another language later:** add it to `config/localization.php`, create `lang/<code>.json` and `lang/<code>/*.php`
  (copy `lang/rw/`), and it appears in the switcher; content translations use the same `translations` column.

