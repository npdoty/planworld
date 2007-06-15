class Theme < ActiveRecord::Base
  def extras
    # /* layer treatments to the original skin (uses absolute positioning) */
    # if (file_exists($_base . "layout/{$skin['id']}/themes/{$theme}/extras.inc")) {
    #   include($_base . "layout/{$skin['id']}/themes/{$theme}/extras.inc");
    # }
  end
end
