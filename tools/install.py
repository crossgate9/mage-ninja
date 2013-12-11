# Install any structure folder to a Magento Installation, such as theme, extension. 

import sys, os, shutil

def cpExt(extPath, mgtPath):
    for root,dirs,files in os.walk(extPath):
        # exclude the git
        if '.git' in dirs:
            dirs.remove('.git')
        
        for dirpath in dirs:
            dirname_ext = os.path.join(root, dirpath)
            suffix = dirname_ext[len(extPath):]
            dirname_mgt = mgtPath + suffix
            if (not os.path.exists(dirname_mgt)):
                os.mkdir(dirname_mgt);
        for filepath in files:
            filename_ext = os.path.join(root, filepath)
            suffix = filename_ext[len(extPath):]
            filename_mgt = mgtPath + suffix
            shutil.copyfile(filename_ext, filename_mgt)

extPath = sys.argv[1]
mgtPath = sys.argv[2]

cpExt(extPath, mgtPath)

# Usage: python install.py [Extension Path] [Magento Path] 
# python install.py ~/dev/extensions/a-magento-extension ~/dev/a-magento-installation/
# don't miss the trailling slash
