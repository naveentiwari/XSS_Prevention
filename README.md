#Project - XSS prevention

##Author
- Naveen Tiwari
- Namratha Putta

##Changes that this library will cause

###Blacklisted Tags
- script
- meta

###Modification for Comment 
- If the user writes '-->' in any data section, it would be replaced to ->, this is done to avoid closing of any opened comment in HTML.
- If the user writes '<!--' in any data then it would be replaced by &lt;!--.
