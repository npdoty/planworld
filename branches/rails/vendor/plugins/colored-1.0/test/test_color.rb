require 'test/unit'
require File.dirname(__FILE__) + '/../lib/color'

class TestColor < Test::Unit::TestCase
  def test_one_color
    assert_equal "\e[31mred\e[0m", "red".red
  end

  def test_two_colors
    assert_equal "\e[34m\e[31mblue\e[0m\e[0m", "blue".red.blue
  end

  def test_background_color
    assert_equal "\e[43mon yellow\e[0m", "on yellow".on_yellow
  end

  def test_hot_color_on_color_action
    assert_equal "\e[31m\e[44mred on blue\e[0m", "red on blue".red_on_blue 
  end

  def test_modifier
    assert_equal "\e[1mway bold\e[0m", "way bold".bold
  end

  def test_modifiers_stack
    assert_equal "\e[4m\e[1munderlined bold\e[0m\e[0m", "underlined bold".bold.underline
  end

  def test_modifiers_stack_with_colors
    assert_equal "\e[36m\e[4m\e[1mcyan underlined bold\e[0m\e[0m\e[0m", "cyan underlined bold".bold.underline.cyan
  end
end
