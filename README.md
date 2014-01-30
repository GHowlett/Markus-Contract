Markus-Contract
===============

A. Copy db to a temporary db for backup. 

B. Clean redundant data
  1. subtopics table - remove all rows with stage!=1
  2. cells table - remove all rows with subtopic_id that doesn't exist in subtopics table
  3. cells_to_phrases table - remove all rows with cell_id that doesn't exist in cells table
  4. phrases table - remove all rows with phrase_id that doesn't exist in cells_to_phrases table
  5. phrase_distractors table- remove all rows with phrase_id that doesn't exist in phrases table
  6. blank_answers table - remove all rows with phrase_id that doesn't exist in phrases table
  7. blank_distractors table - remove all rows with blank_id that doesn't exist in blank_answers table
  8. cells_comprehension table - remove all rows with cell_id that doesn't exist in cells table
  9. comprehension table - remove all rows with comprehension_id that doesn't exist in cells_comprehension table
  10. comprehension_distractors table - remove all rows with comprehension_id that doesn't exist in comprehension table.

C. dump the result into an sqlite file on the server

D. refresh cloud front server via their api.
