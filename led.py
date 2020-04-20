import sys
from blinkt import set_clear_on_exit, set_brightness, set_pixel, show

set_clear_on_exit(False)
#set_brightness(0.05)
set_brightness(0.2)
#set_brightness(1)

for pixel, color in enumerate(sys.argv):

  if pixel > 0:

    if color == 'R': R = 50; G = 0; B = 0
    elif color == 'G': R = 0; G = 10; B = 0
    elif color == 'B': R = 0; G = 0; B = 50
    elif color == 'O': R = 50; G = 10; B = 0
    else: R = 0; G = 0; B = 0

    set_pixel(pixel - 1, R, G, B)

show()
