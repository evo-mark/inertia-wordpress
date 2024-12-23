### 1.2.1

- **BugFix**: Correct implicit null parameters

### 1.2.0

- **Feature**: Add support for React dev server

### 1.1.1

- **BugFix**: Don't do strict boolean checking on `did_action`

### 1.1.0

- **Improvement**: Allow script injection if already on the enqueue hook
- **Improvement**: Readme and licence file updates

### 1.0.1

- **BugFix**: New Vite5 manifest structure causing error

### 0.3.1

- Refactored usort functions to use integer returns
- Fixed instances of function values being passed to functions that require a reference

### 0.3.0

- **BREAKING CHANGE**: Changed the entry class (WpVite) into an instantiable class rather than static since the latter broke multiple uses of the enqueue function
- Removed call to `run` from the plugin entry. Setup is now done when you instantiate the class for the first time
- Added static `$init` property to ensure that the script tag filter is only applied once.

### 0.2.5

- Renamed assets banner
- Extracted changelog to separate .md file
- Added regex for release asset

### 0.2.4

- Readme fixes for params

### 0.2.3

- Added version to entry file
- Attempt fix for param table

### 0.2.2

- Screenshot added to assets
- Updated readme file

### 0.2.1

- Added 'admin' param for loading scripts on admin pages

### 0.2.0

- Added version cache-busting to both scripts and styles

### 0.1.0

- Initial release
