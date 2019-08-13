#!/usr/bin/swift

import Foundation

public func removeIfExists(atPath path: String) throws {
    if FileManager.default.fileExists(atPath: path) {
        print("Removing \(path)")
        try FileManager.default.removeItem(atPath: path)
    }
}

try removeIfExists(atPath: "./.bundle")
try removeIfExists(atPath: "./.swiftlint-ci.yml")
try removeIfExists(atPath: "./Frameworks/.swiftlint.yml")
